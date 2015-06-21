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
abstract class Theme
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
	 *
	 * @return array
	 */
	public abstract function getJSFiles();

	/**
	 * Returns all static files that are necessary.
	 *
	 * @return array
	 */
	public abstract function getAllStaticFiles();

	/**
	 * Returns the name.
	 *
	 * @return string
	 */
	public function getName()
	{
		$refC = new \ReflectionClass($this);
		return $refC->getShortName();
	}

	/**
	 * Returns all themes that this theme is based on.
	 *
	 * @return Array
	 */
	public function getThemeHierachy()
	{
		$themes = array();
		$refC = new \ReflectionClass($this);
		$name = $refC->getShortName();
		$themes[] = $name;
		$c = 0;
		while ($name != "Skelpo\\Framework\\Theme\\Theme" && $c <= 10)
		{
			$parent = $refC->getParentClass();
			$name = $parent->getName();
			$refC = new \ReflectionClass($name);
			
			if ($name != "Skelpo\\Framework\\Theme\\Theme")
				$themes[] = $refC->getShortName();
			$c ++;
		}
		return $themes;
	}

	/**
	 * Returns the paths to this theme.
	 *
	 * @return array
	 */
	public function getPaths()
	{
		$paths = array();
		$baseDir = $this->kernel->getThemeDir();
		$themes = $this->getThemeHierachy();
		foreach ($themes as $theme)
		{
			$paths[] = $baseDir . $theme . "/";
		}
		return $paths;
	}

	/**
	 * An empty function to provide the theme the possibility to modify
	 * some smarty parameters.
	 *
	 * @param \Smarty $s
	 */
	public function fixSmarty(\Smarty $s)
	{
		// intentially empty
	}
}
