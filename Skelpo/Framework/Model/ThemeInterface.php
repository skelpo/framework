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
namespace Skelpo\Framework\Model;

/**
 * Plugin interface to ensure our theme model has all we need.
 */
interface ThemeInterface
{

	/**
	 * Returns the id.
	 *
	 * @return integer
	 */
	public function getId();

	/**
	 * Returns the slug.
	 *
	 * @return string
	 */
	public function getSlug();

	/**
	 * Returns the title.
	 *
	 * @return string
	 */
	public function getTitle();

	/**
	 * Returns the name.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Returns the author.
	 *
	 * @return string
	 */
	public function getAuthor();

	/**
	 * Returns the version.
	 *
	 * @return string
	 */
	public function getVersion();

	/**
	 * Returns if the plugin is active.
	 *
	 * @return string
	 */
	public function isActive();
}