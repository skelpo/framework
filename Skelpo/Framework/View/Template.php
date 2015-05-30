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
namespace Skelpo\Framework\View;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Form;
use Skelpo\Framework\Events\ControllerEvent;
use Skelpo\Framework\Framework;
use Skelpo\Framework\Language\Language;
// use Skelpo\Framework\Forms\Form;

/**
 * A simple template build on top of smarty.
 */
class Template extends \Smarty
{
	/**
	 * The framework instance.
	 */
	protected $framework;
	/**
	 * The current template file.
	 * (not the theme)
	 */
	protected $templateFile;
	/**
	 * The file system.
	 */
	protected $filesystem;

	/**
	 * Creates a new view.
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
	 */
	public function getFramework()
	{
		return $this->framework;
	}

	/**
	 * Returns the content of our specific file.
	 */
	public function getContent()
	{
		$content = $this->fetch($this->templateFile);
		return $content;
	}

	/**
	 * Does this template exist?
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
	 */
	public function setTemplateFile($t)
	{
		$this->templateFile = $t;
	}

	/**
	 * Returns the template file.
	 */
	public function getTemplateFile()
	{
		return $this->templateFile;
	}
}
