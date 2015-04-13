<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.input.php
 * Type:     function
 * Name:     eightball
 * Purpose:  outputs a random magic answer
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
	//$keys = array_keys($element);
	return "input: ".print_r($element->getConfig()->getType()->getName(),true); //->templateFile;
    $answers = array('Yes',
                     'No',
                     'No way',
                     'Outlook not so good',
                     'Ask again soon',
                     'Maybe in your reality');

    $result = array_rand($answers);
    return $answers[$result];
}
?>