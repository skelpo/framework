<?php

/**
 * This file is part of the skelpo framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @version 1.1.0
 * @author Ralph Kuepper <ralph.kuepper@skelpo.com>
 * @copyright 2016 Skelpo Inc. www.skelpo.com
 */

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File: function.input.php
 * Type: function
 * Name: input
 * Purpose: shows a normal input tag
 * -------------------------------------------------------------
 */
/**
 * Renders an input in smarty.
 *
 * @param string[] $params
 * @param Smarty_Internal_Template $template
 * @return string
 */
function smarty_function_input($params, Smarty_Internal_Template $template)
{
	$name = $params['name'];
	$form = $template->smarty->getForm("Form_" . $template->getTemplateVars("currentForm"));
	$element = $form->get($name);
	$view = $form->createView();
	$renderer = $template->getFormRenderer();
	$c = $renderer->renderInput($element, "Form_" . $template->getTemplateVars("currentForm"), $params);
	return $c;
}
