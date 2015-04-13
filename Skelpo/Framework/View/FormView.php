<?php

namespace Skelpo\Framework\View;

use Skelpo\Framework\Forms\FormType;
use Skelpo\Framework\Framework;

class FormView {
	
	protected $framework;
	
	public function __construct(Framework $f)
	{
		$this->framework = $f;
	}
	
	
	public function render(FormType $f)
	{
		$tpl = $this->framework->getModule()."/forms/form.tpl";
		return $tpl;
		
	}
}
