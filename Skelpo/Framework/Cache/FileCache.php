<?php

namespace Skelpo\Framework\Cache;

use Symfony\Component\Filesystem\Filesystem;

class FileCache implements Cacheable
{
	protected $file;
	protected $content;
	protected $framework;
	protected $lifetime;
	protected $filesystem;
	public function __construct($framework,$name, $lifetime = 3600)
	{
		$this->framework = $framework;
		$this->file = $this->framework->getCacheDir()."filecache_".$name.".php";
		$this->lifetime = $lifetime;
		$this->filesystem = new Filesystem();
		$this->load();
		
	}
	public function needUpdate()
	{
		if ($this->lifetime == 0) return false;
		if (!$this->exists()) return true;
		if (filemtime($this->file)<(time()+$this->lifetime))
		{
			$this->clear();
			return true;
		}
	}
	public function load()
	{
		if ($this->exists())
		{
			
			$this->content = file_get_contents($this->file);
		}
	}
	public function setLifetime($t)
	{
		$this->lifetime = $t;
	}
	public function getLifetime()
	{
		return $this->lifetime;
	}
	public function setContent($c)
	{
		$this->content = serialize($c);
		$this->save();
	}
	public function clear()
	{
		$this->content = null;
		$this->filesystem->remove($this->file);
	}
	public function exists()
	{
		return $this->filesystem->exists($this->file);
	}
	public function save()
	{
		$phpcode = '<?php $content = "'.addslashes(($this->content)).'"; ?>';
		$this->filesystem->dumpFile($this->file, $this->content);
	}
	public function getContent()
	{
		return unserialize($this->content);
	}
}

?>