 <?php
 
 use Skelpo\Framework\Forms\Form;
 
/*
* Smarty plugin
* -------------------------------------------------------------
* File:     block.translate.php
* Type:     block
* Name:     form
* Purpose:  integrates a form into the document.
* -------------------------------------------------------------
*/
function smarty_block_form($params, $content, &$smarty, &$repeat)
{
	if ($repeat) {
		$smarty->assign("currentForm",$params['name']);
		return;
	}
	if (!isset($params['name']))
	{
		return "<b>No form name given!</b><br /> ".$content;
	}
	$smarty->assign("currentForm","");
	$form = $smarty->getForm("Form_".$params['name']);
	$locale = $smarty->smarty->getRequest()->attributes->get('_locale');
	$view = $form->createView();
	$renderer = $smarty->getFormRenderer();
	$c = $renderer->renderForm($view, $form->getName(), $content, $params, $locale, $smarty->smarty->getDefaultLanguage());
	return $c;
}
?> 