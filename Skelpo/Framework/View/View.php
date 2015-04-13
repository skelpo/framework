<?php
/**
 * This file is part of the skelpo framework.
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * @version 1.0.0-alpha
 * @author Ralph Kuepper <ralph.kuepper@skelpo.com>
 * @copyright 2015 Skelpo Inc. www.skelpo.com
 */

namespace Skelpo\Framework\View;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Form;
use Skelpo\Framework\Events\ControllerEvent;
use Skelpo\Framework\Framework;
use Skelpo\Framework\Language\Language;
use Skelpo\Framework\View\Template;
//use Skelpo\Framework\Forms\Form;


/**
 * View service class. This class takes twig out of symfony and replaces all responses from controllers
 * with a custom reponse that contains the appropiate template.
 * 
 * Exception are controllers in the API module.
 */
class View extends Template
{
	/**
	 * The current module (api/backend/frontend/widgets)
	 */
	private $module;
	/**
	 * Are we minifying javascript?
	 */
	private $minifyJs;
	/**
	 * Are we minifying css?
	 */
	private $minifyCss;
	/**
	 * Our forms.
	 */
	protected $forms;
	
	protected $templates;
	
	private $eventName1;
	private $eventName2;
	
	private $rootUrl;
	
	protected $language;
	protected $router;
	
	/**
	 * Creates a new view.
	 */
	public function __construct(Framework $f, $rootUrl, $router)
	{
		$this->template_class = "\Skelpo\Framework\View\ViewTemplate";
		parent::__construct($f, "");
		$this->minifyJs = true;
		$this->minifyCss = false;
		$this->rootUrl = $rootUrl;
		$this->router = $router;
		$this->language = new Language($this, "de");
		
		$this->setupSmarty();
	}
	
	public function getRouter()
	{
		return $this->router;
	}
	
	/**
	 * Adds a form to this page.
	 */
	public function addForm(Form $f)
	{
		$this->forms[$f->getName()] = $f;
	}
	/**
	 * Returns a form.
	 */
	public function getForm($name)
	{
		return $this->forms[$name];
	}
	
	
	/**
	 * This function will add template dirs. This is necessary for plugins.
	 */
	private function setupTemplateDirs()
	{
		$startDirs = array();
		//$startDirs = $this->framework->getTemplateDirs();
	}
	/**
	 * Do we need to refresh the cache.
	 */
	public function isCacheDue()
	{
		return true; // TODO: Fix this.
	}
	/**
	 * Event that begins before the controllers are called. Initializes
	 * Smarty and sets the template.
	 */
	public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
		
        if (!is_array($controller)) {
            return;
        }
		// this is our controller
		$controller = $controller[0];
		
		// get the request
		$request = $event->getRequest();
		
		// our string
		$s = $request->attributes->get('_controller');
		
		// only if it is to skelpo controllers
		if (!stristr($s,"::")) return;
		
		// get the different parts
		$params = explode('::',$s);
		$actionName = substr($params[1],0,-6);
		$controllerName = substr($params[0],0,-10);
		
		// build the events name
		$this->eventName1 = str_replace("\\","_",$controllerName)."_".ucwords($actionName)."_PreDispatch";
		$this->eventName2 = str_replace("\\","_",$controllerName)."_PreDispatch";
		
		// get the event dispatcher
		$dispatcher = $this->framework->getEventDispatcher();
		
		// create an event
		$cevent = new ControllerEvent($controller, $request);
		
		// dispatch the events
		$dispatcher->dispatch($this->eventName1, $cevent);
		$dispatcher->dispatch($this->eventName2, $cevent);
		
		$response = $cevent->getResponse();
		if ($response instanceof Response)
		{
			die("AF: ".print_r($response,true));
			die("A:".print_r($cevent,true));
		}
		
		
		// the module we are using right now
		$module = substr($controllerName,strpos($controllerName,"Controllers")+12);
		$this->module = strtolower(substr($module,0,strpos($module,"\\")));
		
		// if we are not returning a template stop here
		if (in_array($this->module, array("api", "widgets"))) return;
		
		// the template name
		$templateName = strtolower(str_replace("\\", "/",substr($controllerName,strpos($controllerName,"Controllers")+12)))."/".$actionName.".tpl";
		
		// in case there is no template for an action, we use the index.tpl
		$defaultTemplateName = strtolower(str_replace("\\", "/",substr($controllerName,strpos($controllerName,"Controllers")+12)))."/index.tpl";
		
		// get our template dirs
		$dirs = $this->framework->getTemplateDirs();
		
		// search for the template
		foreach ($dirs as $dir)
		{
			if ($this->filesystem->exists($dir.$templateName))
			{
				$this->templateFile = $templateName;
				break;
			}
		}
		
		$orf = $dir.$templateName;
		
		// if we haven't found the template let's look for the default template
		if ($this->templateFile=="")
		{
			foreach ($dirs as $dir)
			{
				if ($this->filesystem->exists($dir.$defaultTemplateName))
				{
					$this->templateFile = $defaultTemplateName;
					break;
				}
			}
		}
		// if still no template there is a problem
		if ($this->templateFile=="")
		{
			throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("Template does not exist: ".$orf);
		}
    }
	/**
	 * Returns the URL to the less/css-compiled file.
	 */
	private function getLessUrl()
	{
		$p = $this->framework->getRootDir()."static/".$this->module."/css/";
		
		if (!$this->filesystem->exists($p))
		{
			$this->filesystem->mkdir($p);
		}
		
		$cssfile = $p."all.css";
		$cssurl = $this->rootUrl."static/".$this->module."/css/all.css";
		
		// in case we have it in cache just return the url
		if ($this->filesystem->exists($cssfile) && !$this->isCacheDue()) 
		{
			return $cssurl;
		}
		
		// if not go through all the dirs
		$dirs = $this->framework->getTemplateDirs();
		
		// our output
		$allLess = "";
		
		// go through all the dirs to find the all.less
		foreach ($dirs as $dir_)
		{
			$file = $dir_ . $this->module."/_public/less/all.less";
			if ($this->filesystem->exists($file))
			{
				$allLess = $file;
				break;
			}
		}
		
		// if there is none just return an empty string
		if ($allLess=="") return "";
		
		try {
			// the less parser
			$parser = new \Less_Parser();
			
			// are we minifying?
			\Less_Parser::$options['compress'] = $this->minifyCss;
			
			// parse our output
			$parser->parseFile( $allLess, '/static/' );
			
			// and get it as css
			$css = $parser->getCss();
		}
		catch(Exception $e) {
			//TODO: do something with the arrow
		}
		
		// remove the file if it exists
		if ($this->filesystem->exists($cssfile)) $this->filesystem->remove($cssfile);
		
		// save our output here
		$this->filesystem->dumpFile($cssfile, $css);
		
		// and return the url
		return $cssurl;
	}
	/**
	 * Returns the JS url, compressed and only one file.
	 */
	private function getJSUrl()
	{
		$p = $this->framework->getRootDir()."static/".$this->module."/js/";
		
		if (!$this->filesystem->exists($p))
		{
			$this->filesystem->mkdir($p);
		}
		
		$jsfile = $p."all.js";
		$jsurl = $this->rootUrl."static/".$this->module."/js/all.js";
		
		// check if we have it in cache
		if ($this->filesystem->exists($jsfile) && !$this->isCacheDue()) 
		{
			return $jsurl;
		}
		
		// get all our JS files 
		$files = $this->framework->getTheme()->getJSFiles();
		$files = $files[$this->module];
		
		// and all dirs
		$dirs = $this->framework->getTemplateDirs();
		$dir_ = $dirs[0];
		
		// our output
		$jsoutput = "";
		
		// get all the files
		foreach ($files as $file_)
		{
			$file = $dir_ . $this->module."/_public/js/".$file_;
			if ($this->filesystem->exists($file))
			{
				$jsoutput.=file_get_contents($file)."\n";
				
			}
		}
		// shall we minify?
		if ($this->minifyJs) $jsoutput = \JShrink\Minifier::minify($jsoutput, array('flaggedComments' => false));
		
		// delete the existing file
		if ($this->filesystem->exists($jsfile)) $this->filesystem->remove($jsfile);
		
		// save the new
		$this->filesystem->dumpFile($jsfile, $jsoutput);
		
		// return the url
		return $jsurl;
	}
	/**
	 * Copies all items from the theme folder that should be moved.
	 */
	private function copyStaticContent()
	{
		$baseFolder = $this->framework->getRootDir()."static/".$this->module."/";
		
		if (!$this->filesystem->exists($baseFolder))
		{
			$this->filesystem->remove($baseFolder);
			$this->filesystem->mkdir($baseFolder);
		}
		
		////$fs->symlink('/path/to/source', '/path/to/destination', true);
			
		$files = $this->framework->getTheme()->getAllStaticFiles();
		$files = $files[$this->module];
		
		// and all dirs
		$dirs = $this->framework->getTemplateDirs();
		$dir_ = $dirs[0];
		
		
		// get all the files
		foreach ($files as $file_)
		{
			$file = $dir_ . $this->module."/_public/".$file_;
			$target = $baseFolder.$file_;
			if ($this->filesystem->exists($file))
			{
				$this->filesystem->symlink($file, $target,true);
			}
		}
		
	}
	/**
	 * This event is being triggered after the controllers we called and before the response is checked.
	 * Because our html controllers don't return anything we build the response here.
	 */
	public function onViewResponse(GetResponseForControllerResultEvent $event)
	{
		// get the event dispatcher
		$dispatcher = $this->framework->getEventDispatcher();
		
		// create an event
		$cevent = new ControllerEvent(null, $event->getRequest(), $event->getResponse());
		
		// dispatch the events
		$dispatcher->dispatch($this->eventName1, $cevent);
		$dispatcher->dispatch($this->eventName2, $cevent);
		
		if (in_array($this->module, array("api", "widgets"))) return;
		
		// load language
		$lpaths = array();
		$lpaths[] = "App/Locale/";
		$tpaths = $this->framework->getTemplateDirs();
		foreach ($tpaths as $p)
		{
			$lpaths[] = $p."Locale/";
		}
		$this->language->loadLanguageFiles($lpaths);
		
		// get the compressed urls
		$cssUrl = $this->getLessUrl();
		$jsUrl = $this->getJSUrl();
		
		// assign them to the template
		$this->assign("cssUrl", $cssUrl);
		$this->assign("jsUrl", $jsUrl);
		
		// copy static elements
		if ($this->isCacheDue())
		{
			$this->copyStaticContent();
		}
		
		// get our template
		$content = $this->fetch($this->templateFile);
		
		// parse language elements
		$content = $this->parseLanguage($content);
		
		// our response
		$newResponse = new Response();
		$newResponse->setContent($content);
		$newResponse->setStatusCode(200);
	    $event->setResponse($newResponse);
	}

	protected function parseLanguage($content)
	{
		$ret = preg_replace("/##(.+?)##/e","\$this->language->getString('\\1')",$content);
		return $ret;
	}

}
