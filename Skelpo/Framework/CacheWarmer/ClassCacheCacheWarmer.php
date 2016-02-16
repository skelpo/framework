<?php

/**
 * This file is part of the skelpo framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @version 1.0.0
 * @author Ralph Kuepper <ralph.kuepper@skelpo.com>
 * @author symfony Team
 * @author Tugdual Saunier <tucksaun@gmail.com>
 * @copyright 2016 Skelpo Inc. www.skelpo.com
 */
namespace Skelpo\Framework\CacheWarmer;

use Symfony\Component\ClassLoader\ClassCollectionLoader;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Generates the Class Cache (classes.php) file.
 */
class ClassCacheCacheWarmer implements CacheWarmerInterface
{

	/**
	 * Warms up the cache.
	 *
	 * @param string $cacheDir The cache directory
	 */
	public function warmUp($cacheDir)
	{
		$classmap = $cacheDir . '/classes.map';
		
		if (! is_file($classmap))
		{
			return;
		}
		
		if (file_exists($cacheDir . '/classes.php'))
		{
			return;
		}
		
		ClassCollectionLoader::load(include ($classmap), $cacheDir, 'classes', false);
	}

	/**
	 * Checks whether this warmer is optional or not.
	 *
	 * @return bool always true
	 */
	public function isOptional()
	{
		return true;
	}
}
