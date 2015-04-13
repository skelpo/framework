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

namespace Skelpo\Framework;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Framework bundle to handle all our stuff.
 */
class Framework extends Bundle
{
	/**
	 * Our event dispatcher to deal with events.
	 */
	private $eventDispatcher;
	
	/**
	 * The environment we are using (dev/prod/test).
	 */
	private $environment;
	
	/**
	 * The kernel.
	 */
	private $kernel;
	
	/**
	 * The module we are using.
	 * frontend / backend / api / widgets / mobile
	 */
	private $module; 
	
	/**
	 * Pass-through: Returns the root dir.
	 */
	public function getRootDir()
	{
		return $this->kernel->getRootDir();
	}
	/**
	 * Returns the module.
	 */
	public function getModule()
	{
		return $this->module;
	}
	/**
	 * Pass-through: Returns the root url.
	 */
	public function getRootUrl()
	{
		return $this->kernel->getRootUrl();
	}
	
	/**
	 * Returns the dir for additional smarty plugins.
	 */
	public function getSmartyPluginDir()
	{
		return $this->getRootDir().'engine/Skelpo/Framework/View/Plugins/';
	}
	/**
	 * Pass-through: Returns the current theme.
	 */
	public function getTheme()
	{
		return $this->kernel->getTheme();
	}
	/**
	 * Pass-through: Returns the theme dir.
	 */
	public function getThemeDir()
	{
		return $this->kernel->getThemeDir();
	}
	/**
	 * Returns all dirs that could contain templates.
	 */
	public function getTemplateDirs()
	{
		$a = $this->getTheme()->getPath();
		$dirs = array();
		$d = $a."/";
		/*$dirs_ = scandir($d);
		foreach ($dirs_ as $da)
		{
			if (is_dir($da))
			{
				$dirs[] = $da;
			}
		}*/
		$dirs[] = $d;
		return $dirs;
		
	}
	/**
	 * Pass-through: Returns the cache dir.
	 */
	public function getCacheDir()
    {
    	return $this->kernel->getCacheDir();
    }
	/**
	 * Creates a new instance with the kernel as an argument.
	 */
	public function __construct($kernel)
	{
		$this->kernel = $kernel;
		$this->eventDispatcher = new EventDispatcher();
		// default
		$this->module = "frontend";
	}
	/**
	 * Returns information.
	 */
	public function getInfo()
	{
		return "Skelpo Inc.";
	}
	/**
	 * Returns the event dispatcher.
	 */
	public function getEventDispatcher()
	{
		return $this->eventDispatcher;
	}
	
}
