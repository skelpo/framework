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
	protected $staticFiles;
	protected $themeFiles;
	protected $jsFiles;

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
		$this->staticFiles = array();
		$this->themeFiles = array();
		$this->jsFiles = array();
	}

	/**
	 * Adds a new static file to be included.
	 *
	 * @param String $file
	 */
	protected function addStaticFile($file, $module)
	{
		$this->staticFiles[$module][] = $file;
	}

	/**
	 * Adds a new static file to be included.
	 *
	 * @param String $file
	 */
	protected function addJsFile($file, $module)
	{
		$this->jsFiles[$module][] = $file;
	}

	/**
	 * Adds a new static file to be included.
	 *
	 * @param String $file
	 */
	protected function addThemeFile($file, $module)
	{
		$this->themeFiles[$module][] = $file;
	}

	/**
	 * Returns all static files.
	 *
	 * @return Array
	 */
	public function getJsFiles()
	{
		return $this->jsFiles;
	}

	/**
	 * Returns all static files.
	 *
	 * @return Array
	 */
	public function getStaticFiles()
	{
		return $this->staticFiles;
	}

	/**
	 * Returns all static files.
	 *
	 * @return Array
	 */
	public function getThemefiles()
	{
		return $this->themeFiles;
	}

	/**
	 * Returns doctrine so that plugins can work with the database.
	 *
	 * @return EntityManager
	 */
	protected function getEntityManager()
	{
		return $this->kernel->getContainer()->get("doctrine.orm.entity_manager");
	}

	/**
	 * Initialize the plugin.
	 */
	public abstract function init();

	/**
	 * Install the plugin
	 */
	public abstract function install();

	/**
	 * Uninstall the plugin.
	 */
	public abstract function uninstall();

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
