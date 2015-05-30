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

/**
 * Parent class for all plugins.
 */
abstract class Plugin
{
	protected $framework;
	protected $kernel;

	public function __construct($framework, $kernel)
	{
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
