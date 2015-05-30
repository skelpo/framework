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
namespace Skelpo\Framework\Cache;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Class that represents a file cache.
 * Can save simple arrays with configuration information.
 */
class FileCache implements Cacheable
{
	protected $file;
	protected $content;
	protected $framework;
	protected $lifetime;
	protected $filesystem;

	/**
	 * Create a new cache.
	 * 
	 * @param \Skelpo\Framework\Framework $framework
	 * @param string $name
	 * @param integer $lifetime
	 */
	public function __construct($framework, $name, $lifetime = 3600)
	{
		$this->framework = $framework;
		$this->file = $this->framework->getCacheDir() . "filecache_" . $name . ".php";
		$this->lifetime = $lifetime;
		$this->filesystem = new Filesystem();
		$this->load();
	}

	/**
	 * Is an update of the cache required?
	 * 
	 * @return bool
	 */
	public function needUpdate()
	{
		if ($this->lifetime == 0)
			return false;
		if (! $this->exists())
			return true;
		if (filemtime($this->file) < (time() + $this->lifetime))
		{
			$this->clear();
			return true;
		}
	}

	/**
	 * Loads the content of the cache.
	 */
	public function load()
	{
		if ($this->exists())
		{
			
			$this->content = file_get_contents($this->file);
		}
	}

	/**
	 * Sets the lifetime for this cache.
	 * 
	 * @param integer $t
	 */
	public function setLifetime($t)
	{
		$this->lifetime = $t;
	}

	/**
	 * Returns the lifetime of this cache.
	 * 
	 * @return integer
	 */
	public function getLifetime()
	{
		return $this->lifetime;
	}

	/**
	 * Sets the content of this cache.
	 * 
	 * @param \Serializable $c
	 */
	public function setContent($c)
	{
		$this->content = serialize($c);
		$this->save();
	}

	/**
	 * Clears this cache.
	 */
	public function clear()
	{
		$this->content = null;
		$this->filesystem->remove($this->file);
	}

	/**
	 * Does this cache (file) exist?
	 */
	public function exists()
	{
		return $this->filesystem->exists($this->file);
	}

	/**
	 * Saves this cache.
	 * Caches are saved as php files.
	 */
	public function save()
	{
		$phpcode = '<?php $content = "' . addslashes(($this->content)) . '"; ?>';
		$this->filesystem->dumpFile($this->file, $this->content);
	}

	/**
	 * Returns the content of this cache.
	 * 
	 * @return
	 *
	 */
	public function getContent()
	{
		return unserialize($this->content);
	}
}

?>