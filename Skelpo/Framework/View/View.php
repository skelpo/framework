<?php

/**
 * This file is part of the skelpo framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @version 1.1.0
 * @author Ralph Kuepper <ralph.kuepper@skelpo.com>
 * @copyright 2016 Skelpo Inc. www.skelpo.com
 */
namespace Skelpo\Framework\View;

use Skelpo\Framework\Events\ControllerEvent;
use Skelpo\Framework\Framework;
use Skelpo\Framework\Language\Language;
use Skelpo\Framework\View\Template;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Leafo\ScssPhp\Compiler;

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
	 * Caching on/off.
	 */
	protected $cacheOn;

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
		$this->template_class = "\Smarty_Internal_Template";
		parent::__construct($f, "");
		$this->minifyJs = false;
		$this->minifyCss = false;
		$this->rootUrl = $rootUrl;
		$this->router = $router;
		$this->defaultLanguage = $defaultLanguage;
		$this->pluginManager = $pluginManager;
		$this->cacheOn = false;
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
	public function setLanguage($locale)
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
	 * Is javascript to be minified?
	 *
	 * @param boolean $b
	 */
	public function setMinifyJs($b)
	{
		$this->minifyJs = $b;
	}

	/**
	 * Is css to be minified?
	 *
	 * @param boolean $b
	 */
	public function setMinifyCss($b)
	{
		$this->minifyCss = $b;
	}

	/**
	 * Returns the language.
	 *
	 * @return Skelpo\Framework\Language\Language
	 */
	public function getLanguage()
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
		if ($this->request instanceof \Symfony\Component\HttpFoundation\Request)
		{
			$language = $this->request->attributes->get('_locale');
			if ($language == "")
				$language = $this->getDefaultLanguage();
			$this->setLanguage($language);
		}
		else
		{
			throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("Skelpo Framework needs at least one request.");
		}
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
	 * Sets the cache on/off.
	 *
	 * @param boolean $on
	 */
	public function setCacheOn($on)
	{
		$this->cacheOn = $on;
	}

	/**
	 * Do we need to refresh the cache.
	 *
	 * @return boolean
	 */
	public function isCacheDue()
	{
		return ! $this->cacheOn;
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
	 * Returns the root url of this application.
	 *
	 * @return string
	 */
	public function getRootUrl()
	{
		return $this->rootUrl;
	}

	/**
	 * Returns the root url to all static files.
	 *
	 * @return string
	 */
	public function getStaticFilesUrl()
	{
		return $this->getRootUrl() . "static/";
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

		// paths with potential scss/less files
		$paths = array();

		// go through all the dirs to find the all.less
		foreach ($dirs as $dir_)
		{
			$file = $dir_ . $this->module->getPathName() . "/_public/" . $this->framework->getTheme()->getCssCompiler() . "/all." . $this->framework->getTheme()->getCssCompiler();
			if ($this->filesystem->exists($file))
			{
				$paths[] = $dir_ . $this->module->getPathName() . "/_public/" . $this->framework->getTheme()->getCssCompiler() . "/";
				$allLess = $file;
				break;
			}
		}

		// if there is none just return an empty string
		if ($allLess == "")
			return "";

		try
		{

			$c = $this->framework->getTheme()->getCssCompiler();
			if ($c == "less" || $c == "") // less is assumed if nothing else is given
			{
				// the less parser
				$parser = new \Less_Parser();

				$vars = array();
				foreach ($this->getTemplateVars() as $k => $v)
				{
					if (! is_object($v) && ! is_array($v))
						$vars[$k] = '\'' . $v . '\'';
				}

				// are we minifying?
				\Less_Parser::$options['compress'] = $this->minifyCss;
				$parser->ModifyVars($vars);

				// parse our output
				$parser->parseFile($allLess, $this->rootUrl . 'static/' . $this->module->getPathName());

				// and get it as css
				$css = $parser->getCss();
			}
			else if ($c == "scss")
			{
				$vars = array();
				foreach ($this->getTemplateVars() as $k => $v)
				{
					if (! is_object($v) && ! is_array($v))
						$vars[$k] = '' . $v . '';
				}

				$scss = new Compiler();
				$scssData = file_get_contents($allLess);
				$scss->setImportPaths($paths);
				$scss->setVariables($vars);
				if ($this->minifyCss)
				{
					$scss->setFormatter("Leafo\ScssPhp\Formatter\Crunched");
				}
				else
				{
					$scss->setFormatter("Leafo\ScssPhp\Formatter\Expanded");
				}
				$css = $scss->compile($scssData);
			}
			else if ($c == "css")
			{
				// TODO: Load all css files and just put them together in one file.
			}
		}
		catch (\Exception $e)
		{
			// TODO: Do something with the error.
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

		// go through all "files" that are folders
		foreach ($files as $a => $file)
		{
			foreach ($dirs as $dir_)
			{
				$f = $dir_ . $this->module->getPathName() . "/_public/js/" . $file;
				if (is_dir($f))
				{
					$dirs[] = $f;
					unset($files[$a]);
					$filesDir = scandir($f);
					foreach ($filesDir as $f2)
					{
						if (substr($f2, - 3) == ".js")
						{
							$files[] = $f . '/' . $f2;
						}
					}
				}
			}
		}

		// now find all the actual files
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
				if (stristr($file, "*"))
				{
					$found = true;
					$d = str_replace("*", "", $file);
					if (is_dir($d))
					{
						$files = scandir($d);
						$target = str_replace("*", "", $target);
						if (! is_dir($target))
							mkdir($target);
						foreach ($files as $file_)
						{
							if ($file_ == "." || $file_ == "..")
								continue;
							if ($this->filesystem->exists($d . $file_))
							{

								$this->filesystem->symlink($file . $file_, $target . $file_, true);
							}
						}
					}
				}
				else
				{
					if ($this->filesystem->exists($file))
					{
						$this->filesystem->symlink($file, $target, true);
						$found = true;
					}
				}
			}
			if ($found == false)
			{
				// we won't throw an error anymore because the file may exist in another dir
				// throw new \InvalidArgumentException($file . " does not exist.");
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
		{
			// simply return since here is nothing needed from the template
			return;
		}

		// final events if cache is due (before the css/js compilation)
		if ($this->isCacheDue())
		{
			// run all theme custom commands
			$this->framework->getTheme()->beforeCompile();
		}

		// get the compressed urls
		$cssUrl = $this->getLessUrl();
		$jsUrl = $this->getJSUrl();

		// assign them to the template
		$this->assign("cssUrl", $cssUrl);
		$this->assign("jsUrl", $jsUrl);

		// final events if cache is due (after the css/js complication)
		if ($this->isCacheDue())
		{
			$this->copyStaticContent();

			// run all theme custom commands
			$this->framework->getTheme()->afterCompile();
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
		$ret = preg_replace_callback("/##(.+?)##/", array(
				$this->language,
				"getString"
		), $content);
		return $ret;
	}
}
