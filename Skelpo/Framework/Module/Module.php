<?php

/**
 * This file is part of the skelpo framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @version 1.1.0
 * @author Ralph Kuepper <ralph.kuepper@skelpo.com>
 * @copyright 2016 Skelpo Inc. www.skelpo.com
 */
namespace Skelpo\Framework\Module;

/**
 * Module class that represents "frontend", "backend" and others.
 *
 * A module is basically a type of sub-bundle that contains:
 * - controllers
 * - views
 * - routes for access
 *
 * Let's say you have a normal website and you want to show some landing page type stuff.
 * You also have your "real" app. And then you also have a backend. All those things are great
 * and need their own structure to some extend. At the same time they do need the same models though.
 * So you have three modules: Frontend, App, Backend
 *
 * You can use modules for things like: apps, widgets and more.
 */
abstract class Module
{
	/**
	 * Name of this module.
	 *
	 * @var string
	 */
	protected $name;
	/**
	 * Is this module based on templates? Some modules (like an API)
	 * are not based on templates.
	 *
	 * @var boolean
	 */
	protected $basedOnTemplates;

	/**
	 * Returns the name of this module.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Returns the name of this module for paths (lower case).
	 *
	 * @return string
	 */
	public function getPathName()
	{
		return strtolower($this->name);
	}

	/**
	 * Returns whether this module is based on templates.
	 *
	 * @return boolean
	 */
	public function isBasedOnTemplates()
	{
		return $this->basedOnTemplates;
	}
}
