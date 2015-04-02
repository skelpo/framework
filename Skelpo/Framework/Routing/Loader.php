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
		$routesFile = $rootPath."cache/routes.php";
		
		if (file_exists($routesFile))
		{
			include($routesFile);
		}
		else {
			
			// build all the routes
			$routesString = "";
			$controllerDirs = array();
			$controllerDirs[] = "App/Controllers/Frontend/";
			$controllerDirs[] = "App/Controllers/Backend/";
			$controllerDirs[] = "App/Controllers/Api/";
			// TODO: Add plugins.
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
							$routesString .= $this->buildRoutesForClass($path,$file, $module);
						}
					}
				}
			}
			$a = fopen($routesFile,"a");
			fwrite($a,"<?php ".$routesString." ?>");
			fclose($a);
			include($routesFile);
		}
		return $routes;
	}
	/**
	 * Internal function to build the routes for a specific class.
	 */
	private function buildRoutesForClass($path,$file, $module)
	{
		$s = "";
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
				
				$s .= $this->getRouteString($moduleStr, strtolower($controller), $functionName, array(), $ctlStr, false, $parameters);
				
			}
		}
		return $s;
		
	}
	/**
	 * Internal class to build the route for a specific action. It build all sub-routes as well as the
	 * language.
	 */
	private function getRouteString($module, $controller, $function, $parameter, $ctlStr, $stop = false, $parameters = array())
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
			$s .= $this->getRouteString($module, "", $function, $parameter, $ctlStr);
		}
		 
		if ($function == "index")
		{
			$s .= $this->getRouteString($module, $controller, "", $parameter, $ctlStr, true);
		}
		
		if ($stop) return $s;
		
		if ($function != "") $function = "/".$function;
		if ($controller != "") $controller = "/".$controller;
		$valid = false;
		
		if ($controller != "" && $function != "") $valid = true;
		if ($controller == "" && $function == "") $valid = true;
		
		if ($valid) {	
			$s .= "\n".'$routes->add("route_'.md5(microtime()).'", new Symfony\Component\Routing\Route(\''.$module.$controller.$function.'\',array(\'_controller\' => \''.$ctlStr.'\')));';
			$s .= "\n".'$routes->add("route_'.md5(microtime()).'", new Symfony\Component\Routing\Route(\'/{_locale}'.$module.$controller.$function.'\',array(\'_controller\' => \''.$ctlStr.'\'),array(\'_locale\'=>\''.$this->locale.'\'),array(\'_locale\'=>\''.implode(",",$this->supportedLocales).'\')));';
			$para = "";
			foreach ($parameters as $p)
			{
				$para .= "/{".$p->name."}";
				$s .= "\n".'$routes->add("route_'.md5(microtime()).'", new Symfony\Component\Routing\Route(\''.$module.$controller.$function.$para.'\',array(\'_controller\' => \''.$ctlStr.'\')));';
				$s .= "\n".'$routes->add("route_'.md5(microtime()).'", new Symfony\Component\Routing\Route(\'/{_locale}'.$module.$controller.$function.$para.'\',array(\'_controller\' => \''.$ctlStr.'\'),array(\'_locale\'=>\''.$this->locale.'\'),array(\'_locale\'=>\''.implode(",",$this->supportedLocales).'\')));';
		
				
			}
		}
		return $s;
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