<?php

namespace Skelpo\Framework\Form;

use Symfony\Component\Form\FormRenderer;
use Skelpo\Framework\Framework;

class SmartyRenderer extends FormRenderer implements SmartyRendererInterface
{
    /**
     * @var SmartyRendererEngineInterface
     */
    private $engine;
	
	public function renderForm(\Symfony\Component\Form\FormView $view, $blockName, $content, $params, $requestLocale, $defaultLocale)
	{
		return $this->engine->renderForm($view, $blockName, $content, $params, $requestLocale, $defaultLocale);
	}
	
	public function renderInput(\Symfony\Component\Form\FormInterface $view, $blockName, $params)
	{
		return $this->engine->renderInput($view, $blockName, $params);
	}

    public function __construct(SmartyRendererEngineInterface $engine, $csrfTokenManager = null)
    {
        parent::__construct($engine, $csrfTokenManager);

        $this->engine = $engine;
    }

    /**
     * {@inheritdoc}
     */
    public function setFramework(Framework $s)
    {
        $this->engine->setFramework($s);
    }
}
