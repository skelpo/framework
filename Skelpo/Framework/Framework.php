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
namespace Skelpo\Framework;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Skelpo\Framework\Module;
use Skelpo\Framework\Model\ThemeInterface;

/**
 * Framework bundle to handle all our stuff.
 * This class is closely connected
 * to the Kernel class. The big difference between the two classes is that the
 * framework class is entirely the framework, without any influence from outsite,
 * while the Kernel class is the core of the application and can be changed in many ways.
 */
class Framework extends Bundle
{
	/**
	 * Our event dispatcher to deal with events.
	 *
	 * @var Symfony\Component\EventDispatcher\EventDispatcher
	 */
	private $eventDispatcher;
	
	/**
	 * The environment we are using (dev/prod/test).
	 *
	 * @var string
	 */
	private $environment;
	
	/**
	 * The kernel.
	 *
	 * @var Skelpo\Framework\Kernel\KernelInterface
	 */
	private $kernel;

	/**
	 * Returns the root dir of the application.
	 * 
	 * @return string
	 */
	public function getRootDir()
	{
		return $this->kernel->getRootDir();
	}

	/**
	 * Returns the module.
	 * 
	 * @return Skelpo\Framework\Module
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Sets the module.
	 *
	 * @param Skelpo\Framework\Module $m
	 */
	public function setModule(Module $m)
	{
		$this->module = $m;
	}

	/**
	 * Returns the root URL for this application.
	 * Consider though that
	 * this is not necessarily the only URL that is valid. Depending on
	 * your domain settings other domains count as well. But this one works
	 * 100% for sure.
	 *
	 * @return string The root URL that is used for this application.
	 */
	public function getRootUrl()
	{
		return $this->kernel->getRootUrl();
	}

	/**
	 * Returns the kernel.
	 * 
	 * @return Skelpo\Framework\Kernel
	 */
	public function getKernel()
	{
		return $this->kernel;
	}

	/**
	 * Returns the dir for additional smarty plugins.
	 * 
	 * @return string
	 */
	public function getSmartyPluginDir()
	{
		return $this->getRootDir() . 'engine/Skelpo/Framework/View/Plugins/';
	}

	/**
	 * Returns the dir for additional plugins.
	 * 
	 * @return string
	 */
	public function getPluginDir()
	{
		return $this->getRootDir() . 'App/Plugins/';
	}

	/**
	 * Returns the current theme.
	 * 
	 * @return Skelpo\Framework\Theme\Theme
	 */
	public function getTheme()
	{
		return $this->kernel->getTheme();
	}

	/**
	 * Returns the theme dir.
	 * 
	 * @return string
	 */
	public function getThemeDir()
	{
		return $this->kernel->getThemeDir();
	}

	/**
	 * Returns all dirs that could contain templates.
	 * 
	 * @return string[]
	 */
	public function getTemplateDirs()
	{
		$dirs = $this->getTheme()->getPaths();
		
		// get the template paths from the plugins
		$pluginPaths = $this->kernel->getContainer()->get('pluginmanager')->getPluginPaths();
		$themeHiearchy = $this->getTheme()->getThemeHierachy();
		foreach ($pluginPaths as $p)
		{
			foreach ($themeHiearchy as $themeName)
			{
				$d = $this->getKernel()->getRootDir() . $p . "Themes/" . $themeName . "/";
				if (is_dir($d))
				{
					$dirs[] = $d;
				}
				else
				{
					$dirs2[] = $d;
				}
			}
		}
		return $dirs;
	}

	/**
	 * Returns the cache dir.
	 * 
	 * @return string
	 */
	public function getCacheDir()
	{
		return $this->kernel->getCacheDir();
	}

	/**
	 * Creates a new instance with the kernel as an argument.
	 *
	 * @param Skelpo\Framework\Kernel\KernelInterface
	 */
	public function __construct(Skelpo\Framework\Kernel\KernelInterface $kernel)
	{
		$this->kernel = $kernel;
		$this->eventDispatcher = new EventDispatcher();
	}

	/**
	 * Returns information.
	 * 
	 * @return string[]
	 */
	public function getInfo()
	{
		return array(
				'author' => "Skelpo Inc." 
		);
	}

	/**
	 * Returns the event dispatcher.
	 * 
	 * @return Symfony\Component\EventDispatcher\EventDispatcher
	 */
	public function getEventDispatcher()
	{
		return $this->eventDispatcher;
	}
}
