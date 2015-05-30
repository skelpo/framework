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

use Skelpo\Framework\Forms\FormType;
use Skelpo\Framework\Framework;

class FormView
{
	protected $framework;

	public function __construct(Framework $f)
	{
		$this->framework = $f;
	}

	public function render(FormType $f)
	{
		$tpl = $this->framework->getModule() . "/forms/form.tpl";
		return $tpl;
	}
}
