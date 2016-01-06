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
namespace Skelpo\Framework\View;

use Skelpo\Framework\Framework;
use Symfony\Component\Filesystem\Filesystem;

/**
 * A simple template build on top of smarty.
 */
class Template extends \Smarty
{
	/**
	 * The framework instance.
	 *
	 * @var Skelpo\Framework\Framework
	 */
	protected $framework;
	/**
	 * The current template file.
	 * (not the theme)
	 *
	 * @var string
	 */
	protected $templateFile;
	/**
	 * The file system.
	 *
	 * @var Symfony\Component\Filesystem\Filesystem
	 */
	protected $filesystem;

	/**
	 * Creates a new view.
	 *
	 * @param Skelpo\Framework\Framework $f The framework instance.
	 * @param string $templateFile The file for this template.
	 */
	public function __construct(Framework $f, $templateFile = "")
	{
		$this->template_class = "\Skelpo\Framework\View\ViewTemplate";
		parent::__construct();
		
		$this->framework = $f;
		$this->templateFile = $templateFile;
		$this->filesystem = new Filesystem();
		
		$this->setupSmarty();
	}

	/**
	 * Returns the framework.
	 *
	 * @return Skelpo\Framework\Framework
	 */
	public function getFramework()
	{
		return $this->framework;
	}

	/**
	 * Returns the content of our specific file.
	 *
	 * @return string
	 */
	public function getContent()
	{
		$content = $this->fetch($this->templateFile);
		return $content;
	}

	/**
	 * Does this template exist?
	 *
	 * @return boolean
	 */
	public function exists()
	{
		if ($this->templateFile == "")
			return false;
		else
		{
			return $this->templateExists($this->templateFile);
		}
	}

	/**
	 * Returns the rendereted file.
	 *
	 * @return String
	 */
	public function render()
	{
		return $this->fetch($this->templateFile);
	}

	/**
	 * Internal class to setup smarty with a bunch of config parameters.
	 */
	protected function setupSmarty()
	{
		$p = $this->framework->getCacheDir() . "smarty/";
		$this->setCacheDir($p);
		$this->setCompileDir($p . "compile/");
		$this->setTemplateDir($this->framework->getTemplateDirs());
		$this->error_reporting = 0;
		$this->clearAllCache();
		$this->caching = \Smarty::CACHING_LIFETIME_CURRENT;
		$this->addPluginsDir(__DIR__ . "/Plugins");
	}

	/**
	 * Change the template file.
	 *
	 * @param string $file
	 */
	public function setTemplateFile($file)
	{
		$this->templateFile = $file;
	}

	/**
	 * Returns the template file.
	 *
	 * @return string
	 */
	public function getTemplateFile()
	{
		return $this->templateFile;
	}
}
