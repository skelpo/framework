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
namespace Skelpo\Framework\Form;

use Symfony\Component\Form\FormRenderer;
use Skelpo\Framework\Framework;

/**
 * Class that renders forms in smarty templates.
 */
class SmartyRenderer extends FormRenderer implements SmartyRendererInterface
{
	/**
	 * The rendering engine.
	 *
	 * @var SmartyRendererEngineInterface
	 */
	private $engine;

	/**
	 * Render a form.
	 *
	 * @param Symfony\Component\Form\FormView $view The view for this form.
	 * @param string $blockName Name of this block.
	 * @param string $content Content for the form.
	 * @param string[] $params Parameters for the form.
	 * @param string $requestLocale The locale (language) from the request.
	 * @param string $defaultLocale The locale that is used if the request locale is not available.
	 * @return string
	 */
	public function renderForm(\Symfony\Component\Form\FormView $view, $blockName, $content, $params, $requestLocale, $defaultLocale)
	{
		return $this->engine->renderForm($view, $blockName, $content, $params, $requestLocale, $defaultLocale);
	}

	/**
	 * Render an input within a form.
	 *
	 * @param Symfony\Component\Form\FormInterface $view The form view for this input.
	 * @param string $blockName Name of the block.
	 * @param string[] $params Parameters of this input.
	 * @return string
	 */
	public function renderInput(\Symfony\Component\Form\FormInterface $view, $blockName, $params)
	{
		return $this->engine->renderInput($view, $blockName, $params);
	}

	/**
	 * Creates a new instance.
	 *
	 * @param Skelpo\Framework\Form\SmartyRendererEngineInterface $engine The rendering engine.
	 * @param object $csrfTokenManager Token manager to manage csrf.
	 */
	public function __construct(SmartyRendererEngineInterface $engine, $csrfTokenManager = null)
	{
		parent::__construct($engine, $csrfTokenManager);
		
		$this->engine = $engine;
	}

	/**
	 * Sets the framework.
	 *
	 * @param Skelpo\Framework\Framework $framework
	 *
	 */
	public function setFramework(Framework $framework)
	{
		$this->engine->setFramework($framework);
	}
}
