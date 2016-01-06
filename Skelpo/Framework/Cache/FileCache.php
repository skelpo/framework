<?php

/**
 * This file is part of the skelpo framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @version 1.0.0
 * @author Ralph Kuepper <ralph.kuepper@skelpo.com>
 * @copyright 2016 Skelpo Inc. www.skelpo.com
 */
namespace Skelpo\Framework\Cache;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Class that represents a file cache.
 * Can save simple arrays and objects with configuration information.
 */
class FileCache implements Cacheable
{
	/**
	 * The file we are using.
	 *
	 * @var string
	 */
	protected $file;
	/**
	 * The content
	 *
	 * @var object
	 */
	protected $content;
	/**
	 * The framework.
	 *
	 * @var Skelpo\Framework\Framework
	 */
	protected $framework;
	/**
	 * Lifetime of this cache.
	 *
	 * @var integer
	 */
	protected $lifetime;
	/**
	 * The filesystem class to work with files.
	 *
	 * @var Symfony\Component\Filesystem\Filesystem
	 */
	protected $filesystem;

	/**
	 * Create a new cache.
	 *
	 * @param Skelpo\Framework\Framework $framework
	 * @param string $name
	 * @param integer $lifetime
	 */
	public function __construct(\Skelpo\Framework\Framework $framework, $name, $lifetime = 3600)
	{
		$this->framework = $framework;
		$this->file = $this->framework->getCacheDir() . "filecache_" . $name . ".php";
		$this->lifetime = $lifetime;
		$this->filesystem = new Filesystem();
		$this->load();
	}

	/**
	 * Does this cache need an update?
	 *
	 * @return boolean
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
	 * Loads this cache.
	 */
	public function load()
	{
		if ($this->exists())
		{
			
			$this->content = file_get_contents($this->file);
		}
	}

	/**
	 * Sets the lifetime of this cache
	 *
	 * @param integer $t
	 */
	public function setLifetime($t)
	{
		$this->lifetime = $t;
	}

	/**
	 * Returns the lifetime of this cache
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
	 * Empties the cache.
	 */
	public function clear()
	{
		$this->content = null;
		$this->filesystem->remove($this->file);
	}

	/**
	 * Does this cache exist?
	 *
	 * @return boolean
	 */
	public function exists()
	{
		return $this->filesystem->exists($this->file);
	}

	/**
	 * Saves this cache.
	 */
	public function save()
	{
		$phpcode = '<?php $content = "' . addslashes(($this->content)) . '"; ?>';
		$this->filesystem->dumpFile($this->file, $this->content);
	}

	/**
	 * Returns the content of this cache.
	 *
	 * @return \Serializable
	 */
	public function getContent()
	{
		if (trim($this->content) == "")
			return null;
		return unserialize($this->content);
	}
}

?>