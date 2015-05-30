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
namespace Skelpo\Framework\Kernel;

/**
 * This interface insures that we can get all paths from the kernel.
 */
interface KernelInterface
{

	/**
	 * Every kernel has this class, but just to be sure.
	 */
	public function getRootUrl();

	/**
	 * Returns the dir of our theme.
	 */
	public function getThemeDir();

	/**
	 * Returns the Theme class.
	 */
	public function getTheme();

	/**
	 * Returns the cache dir.
	 */
	public function getCacheDir();

	/**
	 * Returns the config dir.
	 */
	public function getConfigDir();
}
