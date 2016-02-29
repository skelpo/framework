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
 * File: function.file.php
 * Type: function
 * Name: url
 * Purpose: Formats an URL to a file correctly
 * -------------------------------------------------------------
 */
/**
 * Formats a URL.
 * This function is used like {file action="frontend/index/action"}.
 *
 * @param string[] $params
 * @param Smarty_Internal_Template $template
 * @return string
 */
function smarty_function_file($params, Smarty_Internal_Template $template)
{
	$path = $params['path'];
	$url = $template->smarty->getStaticFilesUrl() . $path;
	return $url;
}
