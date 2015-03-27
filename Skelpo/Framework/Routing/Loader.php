<?php
namespace Skelpo\Framework\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Loader implements LoaderInterface
{
    private $loaded = false;
	private $locale;
	private $supportedLocales;
	private $kernel;
	
	public function __construct($k)
	{
		$this->kernel = $k;
	}

    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Framework can only be loaded once.');
        }
		$routes = new RouteCollection();
		
		$container = new ContainerBuilder();
		$loader = new YamlFileLoader($container, new FileLocator($this->kernel->getConfigDir()));
		$loader->load('parameters.yml');
		//die("asfasdf2");
		$this->locale = $container->getParameter('locale');
		$this->supportedLocales = explode(",", $container->getParameter('supportedLocales'));
		
		if (!in_array($this->locale, $this->supportedLocales))
		{
			$this->locale = $this->supportedLocales[0];
		}
		
		$routes = $this->buildRoutes($routes);

        $this->loaded = true;
		
		$routes->add("frontend", new \Symfony\Component\Routing\Route('/',array('_controller' => 'App\Controllers\Frontend\IndexController::indexAction')));
		$routes->add("backend", new \Symfony\Component\Routing\Route('/backend',array('_controller' => 'App\Controllers\Backend\IndexController::indexAction')));
		$routes->add("api", new \Symfony\Component\Routing\Route('/api',array('_controller' => 'App\Controllers\Api\IndexController::indexAction')));
		$routes->add("frontendlocale", new \Symfony\Component\Routing\Route('/{_locale',array('_controller' => 'App\Controllers\Frontend\IndexController::indexAction'),array('_locale'=>$this->locale),array('_locale'=>implode(",",$this->supportedLocales))));
		$routes->add("backendlocale", new \Symfony\Component\Routing\Route('/{_locale/backend',array('_controller' => 'App\Controllers\Backend\IndexController::indexAction')));
			

        return $routes;
    }

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
			//die("done");
			include($routesFile);
		}
		return $routes;
	}

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
				if ($function->getNumberOfParameters()>0)
				{
					//die("C:".print_r($parameters,true));
				}
				$ctlStr = $controllerNS.'Controller::'.$functionName."Action";
				
				if ($module=="frontend") $module = "";
				else $module = $module . "/";
				
				if ($functionName=="index")
				{
					$s .= '$routes->add("route_'.md5(microtime()).'", new Symfony\Component\Routing\Route(\'/'.$module.strtolower($controller).'\',array(\'_controller\' => \''.$ctlStr.'\')));';
					$s .= '$routes->add("route_'.md5(microtime()).'", new Symfony\Component\Routing\Route(\'/{_locale}/'.$module.strtolower($controller).'\',array(\'_controller\' => \''.$ctlStr.'\'),array(\'_locale\'=>\''.$this->locale.'\'),array(\'_locale\'=>\''.implode(",",$this->supportedLocales).'\')));';
				}
				$s .= '$routes->add("route_'.md5(microtime()).'", new Symfony\Component\Routing\Route(\'/'.$module.strtolower($controller).'/'.$functionName.'\',array(\'_controller\' => \''.$ctlStr.'\')));';
				$s .= '$routes->add("route_'.md5(microtime()).'", new Symfony\Component\Routing\Route(\'/{_locale}/'.$module.strtolower($controller).'/'.$functionName.'\',array(\'_controller\' => \''.$ctlStr.'\'),array(\'_locale\'=>\''.$this->locale.'\'),array(\'_locale\'=>\''.implode(",",$this->supportedLocales).'\')));';
			
				
			}
		}
		return $s;
		
	}
	
    public function supports($resource, $type = null)
    {
        return 'extra' === $type;
    }

    public function getResolver()
    {
        // needed, but can be blank, unless you want to load other resources
        // and if you do, using the Loader base class is easier (see below)
    }

    public function setResolver(LoaderResolverInterface $resolver)
    {
        // same as above
    }
}
?>