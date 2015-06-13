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
namespace Skelpo\Framework\Plugin;

use Skelpo\Framework\Kernel\Kernel;
use Skelpo\Framework\Framework;

/**
 * Parent class for all plugins.
 */
abstract class Plugin
{
	protected $framework;
	protected $kernel;

	/**
	 * Initializes a new plugin.
	 *
	 * @param \Skelpo\Framework\Framework $framework
	 * @param \Skelpo\Framework\Kernel\Kernel $kernel
	 */
	public function __construct(\Skelpo\Framework\Framework $framework, \Skelpo\Framework\Kernel\Kernel $kernel)
	{
		$this->framework = $framework;
		$this->kernel = $kernel;
	}

	/**
	 * Initialize the plugin.
	 */
	protected abstract function init();

	/**
	 * Install the plugin
	 */
	protected abstract function install();

	/**
	 * Uninstall the plugin.
	 */
	protected abstract function uninstall();

	/**
	 * Returns the kernel.
	 * 
	 * @return Kernel
	 */
	protected function getKernel()
	{
		return $this->kernel;
	}

	/**
	 * Returns the framework.
	 * 
	 * @return Framework
	 */
	protected function getFramework()
	{
		return $this->framework;
	}

	/**
	 * This function is called when the plugin is updating.
	 */
	protected function update()
	{
		// intentially empty
	}

	/**
	 *
	 * @param unknown $eventName
	 * @param unknown $function
	 */
	public function subscribeEvent($eventName, $function)
	{
	}
}
