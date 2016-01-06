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

use Skelpo\Framework\Forms\FormType;
use Skelpo\Framework\Framework;

/**
 * This is a view of a form.
 * Loads the content from a template file.
 */
class FormView
{
	/**
	 * The framework instance.
	 *
	 * @var Skelpo\Framework\Framework
	 */
	protected $framework;

	/**
	 * Creates a new form view.
	 *
	 * @param Skelpo\Framework\Framework $framework
	 */
	public function __construct(Framework $framework)
	{
		$this->framework = $framework;
	}

	/**
	 * Renders this form.
	 *
	 * @param FormType $f
	 * @return string
	 */
	public function render(FormType $f)
	{
		$tpl = $this->framework->getModule() . "/forms/form.tpl";
		return $tpl;
	}
}
