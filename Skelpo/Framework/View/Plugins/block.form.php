 <?php
 
 use Skelpo\Framework\Forms\Form;
 
/*
* Smarty plugin
* -------------------------------------------------------------
* File:     block.translate.php
* Type:     block
* Name:     translate
* Purpose:  translate a block of text
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
	$view = $form->createView();
	$renderer = $smarty->getFormRenderer();
	$c = $renderer->renderForm($view, $form->getName(), $content, $params);
	return $c;
	
}
?> 