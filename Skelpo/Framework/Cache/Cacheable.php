<?php

namespace Skelpo\Framework\Cache;

interface Cacheable
{
	public function needUpdate();
	public function setContent($c);
	public function clear();
	public function exists();
	public function save();
	public function getContent();
}
