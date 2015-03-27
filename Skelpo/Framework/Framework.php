<?php

namespace Skelpo\Framework;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Framework extends Bundle
{
	private $eventDispatcher;
	private $environment;
	private $kernel;
	private $module; // frontend / backend / api / widgets / mobile
	
	public function getRootDir()
	{
		return $this->kernel->getRootDir();
	}
	
	public function getRootUrl()
	{
		return $this->kernel->getRootUrl();
	}
	public function getSmartyPluginDir()
	{
		return $this->getRootDir().'engine/Skelpo/Framework/View/Plugins/';
	}
	public function getTheme()
	{
		return $this->kernel->getTheme();
	}
	public function getThemeDir()
	{
		return $this->kernel->getThemeDir();
	}
	public function getTemplateDirs()
	{
		$a = $this->getTheme()->getPath();
		$dirs = array();
		$d = $a."/";
		/*$dirs_ = scandir($d);
		foreach ($dirs_ as $da)
		{
			if (is_dir($da))
			{
				$dirs[] = $da;
			}
		}*/
		$dirs[] = $d;
		return $dirs;
		
	}
	public function getCacheDir()
    {
    	return $this->kernel->getCacheDir();
    }
	public function __construct($kernel)
	{
		$this->kernel = $kernel;
		$this->eventDispatcher = new EventDispatcher();
		
		$this->module = "frontend";
	}
	public function getInfo()
	{
		return "Skelpo Inc.";
	}
	public function getEventDispatcher()
	{
		return $this->eventDispatcher;
	}
}
