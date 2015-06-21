<?php

/**
 * This file is part of the skelpo framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @version 1.0.0
 * @author Ralph Kuepper <ralph.kuepper@skelpo.com>
 * @copyright 2015 Skelpo Inc. www.skelpo.com
 */
namespace Skelpo\Framework\View;

use Skelpo\Framework\View\FormView;
use Skelpo\Framework\Form\SmartyRenderer;
use Skelpo\Framework\Form\SmartyRendererEngine;
use Skelpo\Framework\Routing\Loader;

/**
 * The internal template for smarty.
 */
class ViewTemplate extends \Smarty_Internal_Template
{
	/**
	 * A form view.
	 *
	 * @var Skelpo\Framework\Viwe\FormView
	 */
	protected $formview;
	/**
	 * The smarty renderer.
	 *
	 * @var SmartyRenderer
	 */
	protected $smartyrenderer;
	/**
	 * The rendering engine.
	 *
	 * @var SmartyRendererEngine
	 */
	protected $smartyrendererengine;

	/**
	 * Creates a new instance.
	 *
	 * @param object $template_resource
	 * @param Smarty $smarty
	 * @param string $_parent
	 * @param string $_cache_id
	 * @param string $_compile_id
	 * @param string $_caching
	 * @param string $_cache_lifetime
	 */
	public function __construct($template_resource, $smarty, $_parent = null, $_cache_id = null, $_compile_id = null, $_caching = null, $_cache_lifetime = null)
	{
		parent::__construct($template_resource, $smarty, $_parent, $_cache_id, $_compile_id, $_caching, $_cache_lifetime);
		$this->smartyrendererengine = new SmartyRendererEngine();
		$this->smartyrendererengine->setFramework($smarty->getFramework());
		if (method_exists($smarty, "getRouter"))
			$this->smartyrendererengine->setRouter($smarty->getRouter());
		
		$this->smartyrenderer = new SmartyRenderer($this->smartyrendererengine);
		$this->smartyrenderer->setFramework($smarty->getFramework());
	}

	/**
	 * Returns the form renderer.
	 *
	 * @return SmartyRenderer
	 */
	public function getFormRenderer()
	{
		return $this->smartyrenderer;
	}

	/**
	 * Returns the form view.
	 *
	 * @return Skelpo\Framework\View\FormView
	 */
	public function getFormView()
	{
		return $this->formview;
	}

	/**
	 * Returns a form under a specific name.
	 *
	 * @param string $name Name of the form.
	 * @return Symfony\Component\Form\Form
	 */
	public function getForm($name)
	{
		return $this->smarty->getForm($name);
	}
}
