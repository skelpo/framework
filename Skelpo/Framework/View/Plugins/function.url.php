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
}
?>