<?php

/**
 * This file is part of the skelpo framework. This file has been
 * partially or fully taken from the symfony framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @version 1.0.0
 * @author Ralph Kuepper <ralph.kuepper@skelpo.com>
 * @author symfony Team
 * @copyright 2016 Skelpo Inc. www.skelpo.com
 */
namespace Skelpo\Framework\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Routing\RouterInterface;

class RouterCacheWarmer implements CacheWarmerInterface
{
	protected $router;

	/**
	 * Constructor.
	 *
	 * @param RouterInterface $router A Router instance
	 */
	public function __construct(RouterInterface $router)
	{
		$this->router = $router;
	}

	/**
	 * Warms up the cache.
	 *
	 * @param string $cacheDir The cache directory
	 */
	public function warmUp($cacheDir)
	{
		if ($this->router instanceof WarmableInterface)
		{
			$this->router->warmUp($cacheDir);
		}
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
