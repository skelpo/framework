<?php

namespace Skelpo\Framework\Theme;

use Symfony\Component\HttpKernel\Kernel;

abstract class AbstractTheme
{
	protected $name;
	protected $path;
	protected $lessFiles;
	protected $javascriptFiles;
	protected $kernel;
	
	public function __construct(Kernel $k)
	{
		$this->kernel = $k;
	}
	
	public abstract function getJSFiles();
	
	
	public abstract function getName();
	
	public function getPath()
	{
		return $this->kernel->getThemeDir().$this->getName();
	}
}
