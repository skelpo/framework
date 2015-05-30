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
namespace Skelpo\Framework\Form;

use Symfony\Component\Form\FormRenderer;
use Skelpo\Framework\Framework;

/**
 * Class that renders forms in smarty templates.
 */
class SmartyRenderer extends FormRenderer implements SmartyRendererInterface
{
	/**
	 *
	 * @var SmartyRendererEngineInterface
	 */
	private $engine;

	/**
	 * Render a form.
	 */
	public function renderForm(\Symfony\Component\Form\FormView $view, $blockName, $content, $params, $requestLocale, $defaultLocale)
	{
		return $this->engine->renderForm($view, $blockName, $content, $params, $requestLocale, $defaultLocale);
	}

	/**
	 * Render an input within a form.
	 */
	public function renderInput(\Symfony\Component\Form\FormInterface $view, $blockName, $params)
	{
		return $this->engine->renderInput($view, $blockName, $params);
	}

	/**
	 * Creates a new instance.
	 */
	public function __construct(SmartyRendererEngineInterface $engine, $csrfTokenManager = null)
	{
		parent::__construct($engine, $csrfTokenManager);
		
		$this->engine = $engine;
	}

	/**
	 *
	 * @ERROR!!!
	 *
	 */
	public function setFramework(Framework $s)
	{
		$this->engine->setFramework($s);
	}
}
