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

namespace Skelpo\Framework\Theme;

use Symfony\Component\HttpKernel\Kernel;

/**
 * This is our parent class for every theme.
 */
abstract class AbstractTheme
{
	/**
	 * The name of our theme.
	 */
	protected $name;
	/**
	 * The folder to our theme.
	 */
	protected $folder;
	/**
	 * All files that are written in less.
	 */
	protected $lessFiles;
	/**
	 * All needed javascript files.
	 */
	protected $javascriptFiles;
	/**
	 * The kernel.
	 */
	protected $kernel;
	
	/**
	 * Creates a new theme instance.
	 */
	public function __construct(Kernel $k)
	{
		$this->kernel = $k;
	}
	
	/**
	 * Returns all javascript files that are needed.
	 */ 
	public abstract function getJSFiles();
	
	/**
	 * Returns all static files that are necessary.
	 */
	public abstract function getAllStaticFiles();
	
	/**
	 * Returns the name.
	 */
	public abstract function getName();
	
	/**
	 * Returns the path to this theme.
	 */
	public function getPath()
	{
		return $this->kernel->getThemeDir().$this->getName();
	}
}
