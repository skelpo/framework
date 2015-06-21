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
namespace Skelpo\Framework\Cache;

/**
 * An interface that serves to mark a class as cachable.
 * Caches can be used
 * for any object as long as these functions are implemented. Primarily used
 * to cache things that don't change very often. E.g. plugins, themes etc.
 */
interface Cacheable
{

	/**
	 * Returns whether this version of the object is still valid.
	 *
	 * @return boolean
	 */
	public function needUpdate();

	/**
	 * Sets the content of the cache.
	 * 
	 * @param object $c
	 */
	public function setContent($c);

	/**
	 * Empties the cache.
	 */
	public function clear();

	/**
	 * Does this cache exist?
	 * 
	 * @return boolean
	 */
	public function exists();

	/**
	 * Saves this cache.
	 */
	public function save();

	/**
	 * Returns the saved content.
	 * 
	 * @return object
	 */
	public function getContent();
}
