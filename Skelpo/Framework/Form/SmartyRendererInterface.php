<?php

namespace Skelpo\Framework\Form;

use Symfony\Component\Form\FormRendererInterface;
use Skelpo\Framework\Framework;

interface SmartyRendererInterface extends FormRendererInterface
{
   
    public function setFramework(Framework $s);
}
