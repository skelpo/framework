<?php

/**
 * This file is part of the skelpo framework.
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 *
 * @version 1.1.0
 * @author Ralph Kuepper <ralph.kuepper@skelpo.com>
 * @copyright 2016 Skelpo Inc. www.skelpo.com
 */
namespace Skelpo\Framework\Kernel;

use Skelpo\Framework\Cache\FileCache;
use Skelpo\Framework\Framework;
use Skelpo\Framework\Module\Module;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Kernel class. It basically is the same as it is for symfony standard, we just extended it in order for us to get a few things easier.
 */
abstract class Kernel extends \Symfony\Component\HttpKernel\Kernel
{
	/**
	 * The theme we are using.
	 */
	protected $theme;
	/**
	 * The framework instance.
	 */
	protected $framework;
	/**
	 * Caches for faster access.
	 */
	protected $caches;

	/**
	 * Array of modules.
	 */
	protected $modules;

	/**
	 * Create a new Kernel.
	 *
	 * @param string $environment
	 * @param boolean $debug
	 */
	public function __construct($environment, $debug)
	{
		parent::__construct($environment, $debug);

		$this->framework = new Framework($this);
		$this->modules = array();
	}

	/**
	 * Adds a module.
	 *
	 * @param Module $m
	 */
	public function addModule(Module $m)
	{
		$this->modules[] = $m;
	}

	/**
	 * Returns all available modules.
	 */
	public function getModules()
	{
		return $this->modules;
	}

	/**
	 * Returns a module based on the name
	 *
	 * @param string $name
	 * @return Module
	 */
	public function getModuleByName($name)
	{
		$name_ = strtolower($name);
		foreach ($this->modules as $m)
		{
			if (strtolower($m->getName()) == $name_)
			{
				return $m;
			}
		}
	}

	public function getName()
	{
		if (null === $this->name)
		{
			$this->name = preg_replace('/[^a-zA-Z0-9_]+/', '', basename($this->rootDir));
		}

		return $this->name;
	}

	/**
	 * Returns the cache for a given name.
	 *
	 * @param string $name
	 * @return object
	 */
	public function getCache($name): FileCache
	{
		if (is_null($this->caches))
		{
			$this->caches = array();
		}
		if (array_key_exists($name, $this->caches))
		{
			return $this->caches[$name];
		}
		else
		{
			$this->caches[$name] = new FileCache($this->framework, $name);
			return $this->caches[$name];
		}
	}

	/**
	 * Selects a theme. We are calling getListOfThemes() for that which has to be implemented by sub classes.
	 */
	protected function selectTheme()
	{
		$themes = $this->getListOfThemes();
		$theme = $themes[0];
		$n = "\\Themes\\" . $theme['name'] . "\\" . $theme['name'];
		$ref = new \ReflectionClass($n);
		$this->theme = $ref->newInstance($this);
	}

	/**
	 * Returns a list of all available theme.
	 *
	 * @return array
	 */
	protected abstract function getListOfThemes();

	/**
	 * Handle a request.
	 *
	 * @param Request $request
	 * @param Symfony\Component\HttpKernel\HttpKernelInterface $type
	 * @param string $catch
	 * @return
	 *
	 */
	public function handle(Request $request, $type = \Symfony\Component\HttpKernel\HttpKernelInterface::MASTER_REQUEST, $catch = true)
	{
		if (false === $this->booted)
		{
			$this->boot();
		}
		$this->getHttpKernel()->setContainer($this->getContainer());
		return $this->getHttpKernel()->handle($request, $type, $catch);
	}

	/**
	 * Returns the dir for themes.
	 *
	 * @return string
	 */
	public function getThemeDir()
	{
		return $this->getRootDir() . "Themes/";
	}

	/**
	 * Returns the curren theme.
	 *
	 * @return Theme
	 */
	public function getTheme()
	{
		return $this->theme;
	}

	/**
	 * Returns the framework.
	 *
	 * @return Framework
	 */
	public function getFramework()
	{
		return $this->framework;
	}

	/**
	 * Returns the framework.
	 *
	 * @return Framework
	 */
	public function getEventDispatcher()
	{
		return $this->getContainer()->get("event_dispatcher");
	}

	/**
	 * Returns the root dir of our app.
	 *
	 * @return String
	 */
	public function getRootDir()
	{
		if (null === $this->rootDir)
		{
			$r = new \ReflectionObject($this);
			$this->rootDir = str_replace('\\', '/', dirname($r->getFileName())) . "/../";
		}
		return $this->rootDir;
	}

	/**
	 * Returns the cache dir.
	 *
	 * @return string
	 */
	public function getCacheDir()
	{
		return $this->getRootDir() . 'var/cache/' . $this->getEnvironment() . '/';
	}

	/**
	 * Returns the config dir.
	 *
	 * @return string
	 */
	public function getConfigDir()
	{
		return $this->getRootDir() . 'config/';
	}

	/**
	 * Returns the lgo dir.
	 *
	 * @return string
	 */
	public function getLogDir()
	{
		return $this->getRootDir() . 'var/logs/' . $this->getEnvironment() . '/';
	}

	/**
	 * Registers a new container configuration. We manipulate the path here.
	 *
	 * @param LoaderInterface $loader
	 */
	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		$loader->load($this->getRootDir() . 'config/config_' . $this->getEnvironment() . '.php');
	}

	/**
	 * Returns the root url.
	 */
	public function getRootUrl()
	{
		return $this->getContainer()->getParameter('rootUrl');
	}
}
