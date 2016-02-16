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
namespace Skelpo\Framework\Theme;

use Symfony\Component\HttpKernel\Kernel;

/**
 * This is our parent class for every theme.
 */
abstract class Theme
{
	/**
	 * The name of our theme.
	 *
	 * @var string
	 */
	protected $name;
	/**
	 * The folder to our theme.
	 *
	 * @var string
	 */
	protected $folder;
	/**
	 * All files that are written in less or scss.
	 *
	 * @var string[]
	 */
	protected $lessFiles;
	/**
	 * All needed javascript files.
	 *
	 * @var string[]
	 */
	protected $javascriptFiles;
	/**
	 * The kernel.
	 *
	 * @var Skelpo\Framework\Kernel\Kernel
	 */
	protected $kernel;
	/**
	 * The css compiler, either less or scss.
	 *
	 * @var string
	 */
	protected $cssCompiler;

	/**
	 * Creates a new theme instance.
	 *
	 * @param Skelpo\Framework\Kernel\Kernel $k The kernel.
	 */
	public function __construct(Kernel $k)
	{
		$this->kernel = $k;
	}

	/**
	 * Returns all javascript files that are needed.
	 *
	 * @return string[]
	 */
	public abstract function getJSFiles();

	/**
	 * Returns all static files that are necessary.
	 *
	 * @return string[]
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
	 * Returns the css compiler for this theme.
	 *
	 * @return string
	 */
	public function getCssCompiler()
	{
		return $this->cssCompiler;
	}

	/**
	 * Sets the css compiler.
	 *
	 * @param String $c
	 */
	public function setCssCompiler($c)
	{
		$this->cssCompiler = $c;
	}

	/**
	 * Returns all themes that this theme is based on.
	 *
	 * @return string[]
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
	 * @return string[]
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
