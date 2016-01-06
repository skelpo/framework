<?php

/**
 * This file is part of the skelpo framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @version 1.0.0
 * @author Ralph Kuepper <ralph.kuepper@skelpo.com>
 * @copyright 2016 Skelpo Inc. www.skelpo.com
 */
namespace Skelpo\Framework\Routing;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * This class creates routes for all controllers with all actions.
 * Makes it significantly easier
 * to build further pages and actions.
 */
class Loader implements LoaderInterface
{
	/**
	 * Is the loader already loaded?
	 *
	 * @var boolean
	 */
	private $loaded = false;
	/**
	 * The locale we are using.
	 *
	 * @var string
	 */
	private $locale;
	/**
	 * All supported locales.
	 *
	 * @var string[]
	 */
	private $supportedLocales;
	/**
	 * Our kernel.
	 *
	 * @var Skelpo\Framework\Kernel\Kernel
	 */
	private $kernel;

	/**
	 * Construct the clas.
	 *
	 * @param Skelpo\Framework\Kernel\Kernel $k The kernel.
	 */
	public function __construct(\Skelpo\Framework\Kernel\Kernel $k)
	{
		$this->kernel = $k;
	}

	/**
	 * Load configuration and start building the routes.
	 *
	 * @param object $resource
	 * @param string $type
	 * @throws RuntimeException If the framework is already loaded.
	 * @return Symfony\Component\Routing\Route[]
	 */
	public function load($resource, $type = null)
	{
		if (true === $this->loaded)
		{
			throw new \RuntimeException('Framework can only be loaded once.');
		}
		$routes = new RouteCollection();
		
		$container = new ContainerBuilder();
		$loader = new PhpFileLoader($container, new FileLocator($this->kernel->getConfigDir()));
		$loader->load('parameters.php');
		
		$this->locale = $container->getParameter('locale');
		$this->supportedLocales = explode(",", $container->getParameter('supportedLocales'));
		
		if (! in_array($this->locale, $this->supportedLocales))
		{
			$this->locale = $this->supportedLocales[0];
		}
		
		$routes = $this->buildRoutes($routes);
		
		$this->loaded = true;
		
		return $routes;
	}

	/**
	 * Build the routes for all controllers.
	 *
	 * @param Symfony\Component\Routing\Route[] $routes The routes that are already established.
	 * @return The routes as a RouteCollection.
	 */
	private function buildRoutes($routes)
	{
		$rootPath = $this->kernel->getRootDir();
		
		$modules = $this->kernel->getModules();
		// build all the routes
		$controllerDirs = array();
		foreach ($modules as $module)
		{
			$controllerDirs[] = "App/Controllers/" . $module->getName() . "/";
		}
		
		$pluginDir = $this->kernel->getFramework()->getPluginDir();
		
		$file = $this->kernel->getCache("plugins");
		$plugins = $file->getContent();
		if ($plugins == "")
		{
			$plugins = array();
		}
		
		foreach ($plugins as $p)
		{
			$pf = $pluginDir . $p['name'] . "/";
			if (is_dir($pf))
			{
				
				foreach ($modules as $module)
				{
					if (is_dir($pf . "Controllers/" . $module->getName() . "/"))
					{
						$controllerDirs[] = "App/Plugins/" . $p['name'] . "/Controllers/" . $module->getName() . "/";
					}
				}
			}
		}
		$lookFor = "Controller.php";
		foreach ($controllerDirs as $dir)
		{
			$path = $rootPath . $dir;
			
			if (! is_dir($path))
				continue;
			
			$module = substr($path, strpos($path, "Controllers/") + 12);
			$module = strtolower(substr($module, 0, - 1));
			
			$files = scandir($path);
			foreach ($files as $file)
			{
				$l = strlen($file);
				if ($l > 14)
				{
					if (substr($file, $l - 14) == $lookFor)
					{
						$this->buildRoutesForClass($path, $file, $module, $routes);
					}
				}
			}
		}
		
		return $routes;
	}

	/**
	 * Internal function to build the routes for a specific class.
	 *
	 * @param string $path
	 * @param string $file
	 * @param string $module
	 * @param Route[] $routes
	 */
	private function buildRoutesForClass($path, $file, $module, $routes)
	{
		$class = str_replace(".php", "", $file);
		include_once ($path . $file);
		
		$content = file_get_contents($path . $file);
		$n = substr($content, strpos($content, "namespace ") + 10);
		$n = substr($n, 0, strpos($n, ";"));
		$controller = str_replace("Controller", "", $class);
		$class = $n . "\\" . $class;
		$controllerNS = $n . "\\" . $controller;
		
		$_reflection = new \ReflectionClass($class);
		$functions = $_reflection->getMethods();
		
		foreach ($functions as $function)
		{
			if (substr($function->getName(), strlen($function->getName()) - 6) == "Action")
			{
				$functionName = str_replace("Action", "", $function->getName());
				$parameters = $function->getParameters();
				
				$ctlStr = $controllerNS . 'Controller::' . $functionName . "Action";
				
				$moduleStr = $module;
				
				$this->buildRoutesIntern($moduleStr, strtolower($controller), $functionName, array(), $ctlStr, false, $parameters, $routes);
			}
		}
	}

	/**
	 * Internal class to build the route for a specific action.
	 * It build all sub-routes as well as the
	 * language.
	 *
	 * @param string $module
	 * @param string $controller
	 * @param string $function
	 * @param string $parameter
	 * @param string $ctlStr
	 * @param string $stop
	 * @param string $parameters
	 * @param Route[] $routes
	 * @return Route[]
	 */
	private function buildRoutesIntern($module, $controller, $function, $parameter, $ctlStr, $stop, $parameters, $routes)
	{
		if ($module == "frontend")
			$module = "";
		else
		{
			if (! stristr($module, "/"))
			{
				$module = "/" . $module;
			}
		}
		
		$s = "";
		
		if ($controller == "index")
		{
			$s .= $this->buildRoutesIntern($module, "", $function, $parameter, $ctlStr, false, array(), $routes);
		}
		
		if ($function == "index")
		{
			$s .= $this->buildRoutesIntern($module, $controller, "", $parameter, $ctlStr, true, array(), $routes);
		}
		
		if ($stop)
			return $s;
		
		if ($function != "")
			$function = "/" . $function;
		if ($controller != "")
			$controller = "/" . $controller;
		$valid = false;
		
		if ($controller != "" && $function != "")
			$valid = true;
		if ($controller == "" && $function == "")
			$valid = true;
		
		if ($valid)
		{
			$routes->add($module . $controller . $function, new \Symfony\Component\Routing\Route($module . $controller . $function, array(
					'_controller' => $ctlStr 
			)));
			$routes->add('/{_locale}' . $module . $controller . $function, new \Symfony\Component\Routing\Route('/{_locale}' . $module . $controller . $function . '', array(
					'_controller' => $ctlStr,
					'_locale' => $this->locale 
			), array(
					'_locale' => implode("|", $this->supportedLocales) 
			)));
			$para = "";
			foreach ($parameters as $p)
			{
				$para .= "/{" . $p->name . "}";
				
				$routes->add($module . $controller . $function . $para, new \Symfony\Component\Routing\Route($module . $controller . $function . $para, array(
						'_controller' => $ctlStr 
				)));
				$routes->add('/{_locale}' . $module . $controller . $function . $para, new \Symfony\Component\Routing\Route('/{_locale}' . $module . $controller . $function . $para, array(
						'_controller' => $ctlStr,
						'_locale' => $this->locale 
				), array(
						'_locale' => implode("|", $this->supportedLocales) 
				)));
			}
		}
	}

	/**
	 * Tells symfony what type this load is.
	 *
	 * @param object $resource
	 * @param string $type
	 * @return boolean
	 */
	public function supports($resource, $type = null)
	{
		return 'extra' === $type;
	}

	/**
	 * Has to be supported for the interface.
	 */
	public function getResolver()
	{
		// needed, but can be blank, unless you want to load other resources
		// and if you do, using the Loader base class is easier (see below)
	}

	/**
	 * Not necessary.
	 *
	 * @param LoaderResolverInterface $resolver
	 */
	public function setResolver(LoaderResolverInterface $resolver)
	{
		// same as above
	}
}
?>