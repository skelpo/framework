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
use Skelpo\Framework\Events\ControllerEvent;
use Skelpo\Framework\Framework;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Filesystem\Filesystem;

/**
 * View service class. This class takes twig out of symfony and replaces all responses from controllers
 * with a custom reponse that contains the appropiate template.
 * 
 * Exception are controllers in the API module.
 */
class View extends \Smarty
{
	/**
	 * The framework instance.
	 */
	private $framework;
	/**
	 * The current template file. (not the theme)
	 */
	private $templateFile;
	/**
	 * The current module (api/backend/frontend/widgets)
	 */
	private $module;
	/**
	 * The current filesystem.
	 */
	private $filesystem;
	/**
	 * Are we minifying javascript?
	 */
	private $minifyJs;
	/**
	 * Are we minifying css?
	 */
	private $minifyCss;
	
	private $eventName1;
	private $eventName2;
	
	/**
	 * Creates a new view.
	 */
	public function __construct(Framework $f)
	{
		parent::__construct();
			
		$this->framework = $f;
		$this->templateFile = "";
		$this->filesystem = new Filesystem();
		$this->minifyJs = true;
		$this->minifyCss = true;
		
		$this->setupSmarty();
	}
	/**
	 * Internal class to setup smarty with a bunch of config parameters.
	 */
	private function setupSmarty()
	{
		$p = $this->framework->getCacheDir()."smarty/";
		$this->setCacheDir($p);
		$this->setCompileDir($p."compile/");
		$this->setTemplateDir($this->framework->getTemplateDirs());
		$this->error_reporting = 0;
		$this->caching = \Smarty::CACHING_LIFETIME_CURRENT;
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
			throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("Template does not exist.");
		}
    }
	/**
	 * Returns the URL to the less/css-compiled file.
	 */
	private function getLessUrl()
	{
		$cssfile = $this->framework->getRootDir()."static/css/all.css";
		$cssurl = $this->framework->getRootUrl()."static/css/all.css";
		
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
		$jsfile = $this->framework->getRootDir()."static/js/all.js";
		$jsurl = $this->framework->getRootUrl()."static/js/all.js";
		
		// check if we have it in cache
		if ($this->filesystem->exists($jsfile) && !$this->isCacheDue()) 
		{
			return $jsurl;
		}
		
		// get all our JS files 
		$files = $this->framework->getTheme()->getJSFiles();
		
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
		
		// get the compressed urls
		$cssUrl = $this->getLessUrl();
		$jsUrl = $this->getJSUrl();
		
		// assign them to the template
		$this->assign("cssUrl", $cssUrl);
		$this->assign("jsUrl", $jsUrl);
		
		// get our template
		$content = $this->fetch($this->templateFile);
		
		// our response
		$newResponse = new Response();
		$newResponse->setContent($content);
		$newResponse->setStatusCode(200);
	    $event->setResponse($newResponse);
	}

}
