<?php

namespace Skelpo\Framework\Form;

use Symfony\Component\Form\FormRendererEngineInterface;
use Skelpo\Framework\Framework;

interface SmartyRendererEngineInterface extends FormRendererEngineInterface
{
    public function setFramework(Framework $s);
}
