<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.input.php
 * Type:     function
 * Name:     input
 * Purpose:  shows a normal input tag
 * -------------------------------------------------------------
 */
function smarty_function_input($params, Smarty_Internal_Template $template)
{
	$name = $params['name'];
	$form = $template->smarty->getForm("Form_".$template->getTemplateVars("currentForm"));
	$element = $form->get($name);
	$view = $form->createView();
	$renderer = $template->getFormRenderer();
	$c = $renderer->renderInput($element, $element->getName(), $params);
	return $c;
}
?>