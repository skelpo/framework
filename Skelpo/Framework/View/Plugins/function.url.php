<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.url.php
 * Type:     function
 * Name:     url
 * Purpose:  Formats an URL correctly
 * -------------------------------------------------------------
 */
function smarty_function_url($params, Smarty_Internal_Template $template)
{
	$action = $params['action'];
	$router = $template->smarty->getRouter();
	$locale = $template->smarty->getRequest()->attributes->get('_locale');
	$p = array();
	$p['_locale'] = $locale;
	return $router->generate($action, $p, $router::ABSOLUTE_PATH, $template->smarty->getDefaultLanguage());
	return get_class($router);
	$form = $template->smarty->getForm("Form_".$template->getTemplateVars("currentForm"));
	$element = $form->get($name);
	$view = $form->createView();
	$renderer = $template->getFormRenderer();
	$c = $renderer->renderInput($element, $element->getName(), $params);
	return $c;
	
}
?>