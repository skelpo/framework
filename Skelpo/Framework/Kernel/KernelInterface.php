<?php

namespace Skelpo\Framework\Kernel;

interface KernelInterface
{
	public function getRootUrl();
	public function getThemeDir();
	public function getTheme();
	public function getCacheDir();
	public function getConfigDir();
}
