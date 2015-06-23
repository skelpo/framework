<?php

/**
 * This file is part of the skelpo framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @version 1.0.0
 * @author Ralph Kuepper <ralph.kuepper@skelpo.com>
 * @copyright 2015 Skelpo Inc. www.skelpo.com
 */
namespace Skelpo\Framework\View;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Form;
use Skelpo\Framework\Events\ControllerEvent;
use Skelpo\Framework\Framework;
use Skelpo\Framework\Language\Language;
use Skelpo\Framework\View\Template;

/**
 * View service class.
 * This class takes twig out of symfony and replaces all responses from controllers
 * with a custom reponse that contains the appropiate template.
 *
 * Exception are controllers in the API module.
 */
class View extends Template
{
	/**
	 * The current module (api/backend/frontend/widgets)
	 *
	 * @var Skelpo\Framework\Module\Module
	 */
	protected $module;
	/**
	 * Are we minifying javascript?
	 *
	 * @var boolean
	 */
	protected $minifyJs;
	/**
	 * Are we minifying css?
	 *
	 * @var boolean
	 */
	protected $minifyCss;
	/**
	 * Our forms.
	 *
	 * @var Symfony\Component\Form\Form[]
	 */
	protected $forms;
	/**
	 * The name of the first event to fire.
	 *
	 * @var string
	 */
	protected $eventName1;
	/**
	 * The name of the second event to fire.
	 *
	 * @var string
	 */
	protected $eventName2;
	/**
	 * The root url.
	 *
	 * @var string
	 */
	protected $rootUrl;
	/**
	 * The technical name of the template (even if the template is not available, this variable says
	 * what it is supposed to be.
	 *
	 * @var string
	 */
	protected $technicalTemplateName;
	/**
	 * The language used.
	 *
	 * @var Skelpo\Framework\Language\Language
	 */
	protected $language;
	/**
	 * The router used.
	 *
	 * @var Skelpo\Framework\Routing\Router
	 */
	protected $router;
	/**
	 * The request we are dealing with.
	 *
	 * @var Symfony\Component\HttpFoundation\Request
	 */
	protected $request;
	/**
	 * Default language.
	 *
	 * @var string
	 */
	protected $defaultLanguage;
	/**
	 * Plugin manager service.
	 *
	 * @var Skelpo\Framework\Plugin\PluginManager
	 */
	protected $pluginManager;

	/**
	 * Creates a new view.
	 *
	 * @param Framework $f
	 * @param string $rootUrl
	 * @param Skelpo\Framework\Routing\Router $router
	 * @param string $defaultLanguage
	 * @param Skelpo\Framework\Plugin\PluginManager $pluginManager
	 */
	public function __construct(Framework $f, $rootUrl, $router, $defaultLanguage, \Skelpo\Framework\Plugin\PluginManager $pluginManager)
	{
		$this->template_class = "\Skelpo\Framework\View\ViewTemplate";
		parent::__construct($f, "");
		$this->minifyJs = false;
		$this->minifyCss = false;
		$this->rootUrl = $rootUrl;
		$this->router = $router;
		$this->defaultLanguage = $defaultLanguage;
		$this->pluginManager = $pluginManager;
		
		$this->setupSmarty();
	}

	/**
	 * Sets the default language to $defaultLanguage.
	 *
	 * @param string $defaultLanguage The language.
	 */
	protected function setDefaultLanguage($defaultLanguage)
	{
		$this->defaultLanguage = $defaultLanguage;
	}

	/**
	 * Returns the default language.
	 *
	 * @return string
	 */
	public function getDefaultLanguage()
	{
		return $this->defaultLanguage;
	}

	/**
	 * Sets the language.
	 * The internal variable $language will be of Skelpo\Framework\Language\Language type.
	 *
	 * @param string $locale (en,de)
	 */
	protected function setLanguage($locale)
	{
		$this->language = new Language($this, $locale);
		// load language
		$lpaths = array();
		$lpaths[] = "App/Locale/";
		$tpaths = $this->framework->getTemplateDirs();
		
		foreach ($tpaths as $p)
		{
			$lpaths[] = $p . "Locale/";
		}
		$pluginPaths = $this->pluginManager->getPluginPaths();
		foreach ($pluginPaths as $p)
		{
			$lpaths[] = $p . "Locale/";
		}
		$this->language->loadLanguageFiles($lpaths);
	}

	/**
	 * Returns the language.
	 *
	 * @return Skelpo\Framework\Language\Language
	 */
	protected function getLanguage()
	{
		return $this->language;
	}

	/**
	 * Sets the request.
	 *
	 * @param RequestStack $requestStack All requests.
	 */
	public function setRequest(RequestStack $requestStack)
	{
		$this->request = $requestStack->getCurrentRequest();
		$language = $this->request->attributes->get('_locale');
		if ($language == "")
			$language = $this->getDefaultLanguage();
		$this->setLanguage($language);
	}

	/**
	 * Returns the router (needed for smarty plugins).
	 *
	 * @return Skelpo\Framework\Routing\Router
	 */
	public function getRouter()
	{
		return $this->router;
	}

	/**
	 * Adds a form to this page.
	 *
	 * @param Symfony\Component\Form\Form $f
	 */
	public function addForm(Form $f)
	{
		$this->forms[$f->getName()] = $f;
	}

	/**
	 * Returns a form.
	 *
	 * @param string $name
	 * @return Symfony\Component\Form\Form
	 */
	public function getForm($name)
	{
		return $this->forms[$name];
	}

	/**
	 * Do we need to refresh the cache.
	 *
	 * @return boolean
	 */
	public function isCacheDue()
	{
		return true; // TODO: Fix this.
	}

	/**
	 * Event that begins before the controllers are called.
	 * Initializes
	 * Smarty and sets the template.
	 *
	 * @param FilterControllerEvent $event The event.
	 */
	public function onKernelController(FilterControllerEvent $event)
	{
		$controller = $event->getController();
		
		if (! is_array($controller))
		{
			return;
		}
		// this is our controller
		$controller = $controller[0];
		
		// get the request
		$request = $event->getRequest();
		
		// our string
		$s = $request->attributes->get('_controller');
		
		// only if it is to skelpo controllers
		if (! stristr($s, "::"))
			return;
			
			// get the different parts
		$params = explode('::', $s);
		$actionName = substr($params[1], 0, - 6);
		$controllerName = substr($params[0], 0, - 10);
		
		// build the events name
		$this->eventName1 = str_replace("\\", "_", $controllerName) . "_" . ucwords($actionName) . "_PreDispatch";
		$this->eventName2 = str_replace("\\", "_", $controllerName) . "_PreDispatch";
		
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
			// $event->setResponse($response);
			return;
		}
		
		// the module we are using right now
		$module = substr($controllerName, strpos($controllerName, "Controllers") + 12);
		$moduleName = strtolower(substr($module, 0, strpos($module, "\\")));
		
		$this->module = $this->framework->getKernel()->getModuleByName($moduleName);
		
		// if we are not returning a template stop here
		if ($this->module->isBasedOnTemplates() == false)
			return;
			
			// the template name
		$templateName = strtolower(str_replace("\\", "/", substr($controllerName, strpos($controllerName, "Controllers") + 12))) . "/" . $actionName . ".tpl";
		
		// in case there is no template for an action, we use the index.tpl
		$defaultTemplateName = strtolower(str_replace("\\", "/", substr($controllerName, strpos($controllerName, "Controllers") + 12))) . "/index.tpl";
		
		// get our template dirs
		$dirs = $this->framework->getTemplateDirs();
		
		// search for the template
		foreach ($dirs as $dir)
		{
			if ($this->filesystem->exists($dir . $templateName))
			{
				$this->templateFile = $templateName;
				break;
			}
		}
		
		// if we haven't found the template let's look for the default template
		if ($this->templateFile == "")
		{
			foreach ($dirs as $dir)
			{
				if ($this->filesystem->exists($dir . $defaultTemplateName))
				{
					$this->templateFile = $defaultTemplateName;
					break;
				}
			}
		}
		
		$this->technicalTemplateName = $dir . $templateName;
	}

	/**
	 * Returns the URL to the less/css-compiled file.
	 *
	 * @return string
	 */
	private function getLessUrl()
	{
		$p = $this->framework->getRootDir() . "static/" . $this->module->getPathName() . "/css/";
		
		if (! $this->filesystem->exists($p))
		{
			$this->filesystem->mkdir($p);
		}
		
		$cssfile = $p . "all.css";
		$cssurl = $this->rootUrl . "static/" . $this->module->getPathName() . "/css/all.css";
		
		// in case we have it in cache just return the url
		if ($this->filesystem->exists($cssfile) && ! $this->isCacheDue())
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
			$file = $dir_ . $this->module->getPathName() . "/_public/less/all.less";
			if ($this->filesystem->exists($file))
			{
				$allLess = $file;
				break;
			}
		}
		
		// if there is none just return an empty string
		if ($allLess == "")
			return "";
		
		try
		{
			// the less parser
			$parser = new \Less_Parser();
			
			// are we minifying?
			\Less_Parser::$options['compress'] = $this->minifyCss;
			
			$vars = array();
			foreach ($this->getTemplateVars() as $k => $v)
			{
				$vars[$k] = '\'' . $v . '\'';
			}
			
			$parser->ModifyVars($vars);
			
			// parse our output
			$parser->parseFile($allLess, '/static/');
			
			// and get it as css
			$css = $parser->getCss();
		}
		catch (\Exception $e)
		{
			// TODO: do something with the error
			$css = "";
		}
		
		// remove the file if it exists
		if ($this->filesystem->exists($cssfile))
			$this->filesystem->remove($cssfile);
			
			// save our output here
		$this->filesystem->dumpFile($cssfile, $css);
		
		// and return the url
		return $cssurl;
	}

	/**
	 * Returns the JS url, compressed and only one file.
	 *
	 * @return string
	 */
	private function getJSUrl()
	{
		$p = $this->framework->getRootDir() . "static/" . $this->module->getPathName() . "/js/";
		
		if (! $this->filesystem->exists($p))
		{
			$this->filesystem->mkdir($p);
		}
		
		$jsfile = $p . "all.js";
		$jsurl = $this->rootUrl . "static/" . $this->module->getPathName() . "/js/all.js";
		
		// check if we have it in cache
		if ($this->filesystem->exists($jsfile) && ! $this->isCacheDue())
		{
			return $jsurl;
		}
		
		// get all our JS files
		$files = $this->framework->getTheme()->getJSFiles();
		if (isset($files[$this->module->getPathName()]))
		{
			$files = $files[$this->module->getPathName()];
		}
		else
		{
			$files = array();
		}
		
		// add the files for the plugins
		$plugins = $this->pluginManager->getPlugins();
		foreach ($plugins as $plugin)
		{
			$pluginFiles = $plugin->getJsFiles();
			if (array_key_exists($this->module->getPathName(), $pluginFiles))
				$files = array_merge($files, $pluginFiles[$this->module->getPathName()]);
		}
		
		// and all dirs
		$dirs = $this->framework->getTemplateDirs();
		
		foreach ($files as $a => $file)
		{
			foreach ($dirs as $dir_)
			{
				$f = $dir_ . $this->module->getPathName() . "/_public/js/" . $file;
				if (file_exists($f))
					$files[$a] = $f;
			}
		}
		
		// get all the files
		$jsoutput = $this->loadFiles($files, ".js");
		
		// shall we minify?
		if ($this->minifyJs)
			$jsoutput = \JShrink\Minifier::minify($jsoutput, array(
					'flaggedComments' => false 
			));
			
			// delete the existing file
		if ($this->filesystem->exists($jsfile))
			$this->filesystem->remove($jsfile);
			
			// save the new
		$this->filesystem->dumpFile($jsfile, $jsoutput);
		
		// return the url
		return $jsurl;
	}

	/**
	 * Loadas all files from the file system with a certain endings.
	 * Works its way recursively through a dictionary.
	 *
	 * @param string[] $files
	 * @param string $ending
	 * @return string
	 */
	private function loadFiles($files, $ending)
	{
		$jsoutput = "";
		foreach ($files as $file)
		{
			if ($this->filesystem->exists($file))
			{
				if (is_dir($file))
				{
					$ff = scandir($file);
					$files_ = array();
					foreach ($ff as $f)
					{
						if ($f == ".." || $f == ".")
							continue;
						$files_[] = $file . "/" . $f;
					}
					$jsoutput .= $this->loadFiles($files_, $ending);
				}
				else
				{
					if (substr($file, - 1 * strlen($ending)) == $ending)
						$jsoutput .= file_get_contents($file) . "\n";
				}
			}
		}
		return $jsoutput;
	}

	/**
	 * Copies all items from the theme folder that should be moved.
	 */
	private function copyStaticContent()
	{
		$baseFolder = $this->framework->getRootDir() . "static/" . $this->module->getPathName() . "/";
		
		if (! $this->filesystem->exists($baseFolder))
		{
			$this->filesystem->remove($baseFolder);
			$this->filesystem->mkdir($baseFolder);
		}
		
		// all dirs from the template
		$dirs = $this->framework->getTemplateDirs();
		
		// our initial static files
		$files = $this->framework->getTheme()->getAllStaticFiles();
		// specify
		if (isset($files[$this->module->getPathName()]))
		{
			$files = $files[$this->module->getPathName()];
		}
		else
		{
			$files = array();
		}
		
		// add the files for the plugins
		$plugins = $this->pluginManager->getPlugins();
		foreach ($plugins as $plugin)
		{
			$pluginFiles = $plugin->getStaticFiles();
			if (array_key_exists($this->module->getPathName(), $pluginFiles))
				$files = array_merge($files, $pluginFiles[$this->module->getPathName()]);
		}
		
		$dirsCount = count($dirs);
		
		// get all the files
		foreach ($files as $f2 => $file_)
		{
			$found = false;
			for($a = 1; $a < $dirsCount + 1; $a ++)
			{
				if (! is_numeric($f2))
				{
					$file = $dirs[$dirsCount - $a] . $this->module->getPathName() . "/_public/" . $f2;
					$target = $baseFolder . $file_;
				}
				else
				{
					$file = $dirs[$dirsCount - $a] . $this->module->getPathName() . "/_public/" . $file_;
					$target = $baseFolder . $file_;
				}
				if ($this->filesystem->exists($file))
				{
					$this->filesystem->symlink($file, $target, true);
					$found = true;
				}
			}
			if ($found == false)
			{
				throw new \InvalidArgumentException($file . " does not exist.");
			}
		}
	}

	/**
	 * This event is being triggered after the controllers we called and before the response is checked.
	 * Because our html controllers don't return anything we build the response here.
	 *
	 * @param GetResponseForControllerResultEvent $event The event.
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
		
		if ($this->module->isBasedOnTemplates() == false)
			return;
			
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
		
		$this->framework->getTheme()->fixSmarty($this);
		
		// if still no template there is a problem
		if ($this->templateFile == "")
		{
			throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("Template does not exist: " . $this->technicalTemplateName);
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

	/**
	 * Parses our content for language elements.
	 *
	 * @param string $content The content to parse.
	 * @return The parsed content.
	 */
	protected function parseLanguage($content)
	{
		$ret = preg_replace("/##(.+?)##/e", "\$this->language->getString('\\1')", $content);
		return $ret;
	}
}
