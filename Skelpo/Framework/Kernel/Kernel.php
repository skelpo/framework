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
namespace Skelpo\Framework\Kernel;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\Loader\LoaderInterface;
use Skelpo\Framework\Framework;
use Skelpo\Framework\Plugin\PluginManager;
use Skelpo\Framework\Kernel\KernelInterface;
use Skelpo\Framework\Cache\FileCache;

/**
 * Kernel class.
 */
abstract class Kernel extends \Symfony\Component\HttpKernel\Kernel
{
	protected $theme;
	protected $framework;
	protected $caches;

	public function __construct($environment, $debug)
	{
		parent::__construct($environment, $debug);
		
		$this->framework = new Framework($this);
	}

	public function getCache($name)
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

	protected function selectTheme()
	{
		$themes = $this->getListOfThemes();
		$theme = $themes[0];
		$n = "\\Themes\\" . $theme['name'] . "\\" . $theme['name'];
		// out of lazyness we just skip doing it properly right now
		$this->theme = new $n($this);
	}

	/**
	 * @ERROR!!! @api
	 */
	public function handle(Request $request, $type = \Symfony\Component\HttpKernel\HttpKernelInterface::MASTER_REQUEST, $catch = true)
	{
		if (false === $this->booted)
		{
			$this->boot();
		}
		
		return $this->getHttpKernel()->handle($request, $type, $catch);
	}

	public function getThemeDir()
	{
		return $this->getRootDir() . "Themes/";
	}

	public function getTheme()
	{
		return $this->theme;
	}

	public function getFramework()
	{
		return $this->framework;
	}

	/**
	 * @ERROR!!! @api
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

	public function getCacheDir()
	{
		return $this->getRootDir() . 'cache/' . $this->getEnvironment() . '/';
	}

	public function getConfigDir()
	{
		return $this->getRootDir() . 'config/';
	}

	public function getLogDir()
	{
		return $this->getRootDir() . 'logs/' . $this->getEnvironment() . '/';
	}

	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		$loader->load($this->getRootDir() . 'config/config_' . $this->getEnvironment() . '.yml');
	}

	public function getRootUrl()
	{
		return $this->getContainer()->getParameter('rootUrl');
	}
}
