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

namespace Skelpo\Framework\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * This class creates routes for all controllers with all actions. Makes it significantly easier
 * to build further pages and actions.
 */
class Loader implements LoaderInterface
{
    private $loaded = false;
	private $locale;
	private $supportedLocales;
	private $kernel;
	
	/**
	 * Construct the clas.
	 */
	public function __construct($k)
	{
		$this->kernel = $k;
	}
	/**
	 * Load configuration and start building the routes.
	 */
    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Framework can only be loaded once.');
        }
		$routes = new RouteCollection();
		
		$container = new ContainerBuilder();
		$loader = new YamlFileLoader($container, new FileLocator($this->kernel->getConfigDir()));
		$loader->load('parameters.yml');
		
		$this->locale = $container->getParameter('locale');
		$this->supportedLocales = explode(",", $container->getParameter('supportedLocales'));
		
		if (!in_array($this->locale, $this->supportedLocales))
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
	 * @return The routes as a RouteCollection.
	 */
	private function buildRoutes($routes)
	{
		$rootPath = $this->kernel->getRootDir();
		
		// build all the routes
		$controllerDirs = array();
		$controllerDirs[] = "App/Controllers/Frontend/";
		$controllerDirs[] = "App/Controllers/Backend/";
		$controllerDirs[] = "App/Controllers/Api/";
		
		$pluginDir = $this->kernel->getFramework()->getPluginDir();
		
		$file = $this->kernel->getCacheDir()."plugins.php";
		if (file_exists($file))
		{
			include($file);
		}
		else {
			$plugins = array();
		}
		
		foreach ($plugins as $p)
		{
			$pf = $pluginDir . $p['name']."/";
			if (is_dir($pf))
			{
				if (is_dir($pf."Controllers/Frontend/"))
				{
					$controllerDirs[] = "App/Plugins/".$p['name']."/Controllers/Frontend/";
				}
				if (is_dir($pf."Controllers/Backend/"))
				{
					$controllerDirs[] = "App/Plugins/".$p['name']."/Controllers/Backend/";
				}
				if (is_dir($pf."Controllers/Api/"))
				{
					$controllerDirs[] = "App/Plugins/".$p['name']."/Controllers/Api/";
				}
				
			}
		}
		
		$lookFor = "Controller.php";
		foreach ($controllerDirs as $dir)
		{
			$path = $rootPath . $dir;
			
			$module = substr($path,strpos($path,"Controllers/")+12);
			$module = strtolower(substr($module,0,-1));
			
			$files = scandir($path);
			foreach ($files as $file)
			{
				$l = strlen($file);
				if ($l>14)
				{
					if (substr($file,$l-14)==$lookFor)
					{
						$this->buildRoutesForClass($path,$file, $module, $routes);
					}
				}
			}
		}
		
		return $routes;
	}
	/**
	 * Internal function to build the routes for a specific class.
	 */
	private function buildRoutesForClass($path,$file, $module, $routes)
	{
		$class = str_replace(".php", "", $file);
		include_once($path.$file);
		
		$content = file_get_contents($path.$file);
		$n = substr($content, strpos($content, "namespace ")+10);
		$n = substr($n,0,strpos($n,";"));
		$controller = str_replace("Controller","",$class);
		$class = $n."\\".$class;
		$controllerNS = $n."\\".$controller;
		
		$_reflection = new \ReflectionClass($class);
		$functions = $_reflection->getMethods();
		
		foreach ($functions as $function)
		{
			if (substr($function->getName(),strlen($function->getName())-6)=="Action")
			{
				$functionName = str_replace("Action","",$function->getName());
				$parameters = $function->getParameters();
				
				$ctlStr = $controllerNS.'Controller::'.$functionName."Action";
				$moduleStr = $module;
				
				$this->buildRoutesIntern($moduleStr, strtolower($controller), $functionName, array(), $ctlStr, false, $parameters, $routes);
				
			}
		}
	}
	/**
	 * Internal class to build the route for a specific action. It build all sub-routes as well as the
	 * language.
	 */
	private function buildRoutesIntern($module, $controller, $function, $parameter, $ctlStr, $stop, $parameters, $routes)
	{
		if ($module == "frontend") $module = "";
		else {
			if (!stristr($module,"/")) 
			{
				$module = "/".$module;
			}
		}
		
		$s = "";
		
		if ($controller=="index")
		{
			$s .= $this->buildRoutesIntern($module, "", $function, $parameter, $ctlStr, false, array(), $routes);
		}
		 
		if ($function == "index")
		{
			$s .= $this->buildRoutesIntern($module, $controller, "", $parameter, $ctlStr, true, array(), $routes);
		}
		
		if ($stop) return $s;
		
		if ($function != "") $function = "/".$function;
		if ($controller != "") $controller = "/".$controller;
		$valid = false;
		
		if ($controller != "" && $function != "") $valid = true;
		if ($controller == "" && $function == "") $valid = true;
		
		if ($valid) {	
			$routes->add($module.$controller.$function, new \Symfony\Component\Routing\Route($module.$controller.$function,array('_controller' => $ctlStr)));
			$routes->add('/{_locale}'.$module.$controller.$function, new \Symfony\Component\Routing\Route('/{_locale}'.$module.$controller.$function.'',array('_controller' => $ctlStr, '_locale'=> $this->locale),array('_locale'=> implode("|",$this->supportedLocales))));
			$para = "";
			foreach ($parameters as $p)
			{
				$para .= "/{".$p->name."}";
				
				$routes->add($module.$controller.$function.$para, new \Symfony\Component\Routing\Route($module.$controller.$function.$para,array('_controller' => $ctlStr)));
				$routes->add('/{_locale}'.$module.$controller.$function.$para, new \Symfony\Component\Routing\Route('/{_locale}'.$module.$controller.$function.$para,array('_controller' => $ctlStr, '_locale'=>$this->locale),array('_locale'=> implode("|",$this->supportedLocales))));
				
			}
		}
	}
	/**
	 * Tells symfony what type this load is.
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
	 */
    public function setResolver(LoaderResolverInterface $resolver)
    {
        // same as above
    }
}
?>