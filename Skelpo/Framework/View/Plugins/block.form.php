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
	use Skelpo\Framework\Forms\Form;

	/*
	 * Smarty plugin
	 * -------------------------------------------------------------
	 * File: block.translate.php
	 * Type: block
	 * Name: form
	 * Purpose: integrates a form into the document.
	 * -------------------------------------------------------------
	 */
	function smarty_block_form($params, $content, &$smarty, &$repeat)
	{
		if ($repeat)
		{
			$smarty->assign("currentForm", $params['name']);
			return;
		}
		if (! isset($params['name']))
		{
			return "<b>No form name given!</b><br /> " . $content;
		}
		$smarty->assign("currentForm", "");
		$form = $smarty->getForm("Form_" . $params['name']);
		$locale = $smarty->smarty->getRequest()->attributes->get('_locale');
		$view = $form->createView();
		
		$renderer = $smarty->getFormRenderer();
		$c = $renderer->renderForm($view, $form->getName(), $content, $params, $locale, $smarty->smarty->getDefaultLanguage());
		return $c;
	}
	?> 