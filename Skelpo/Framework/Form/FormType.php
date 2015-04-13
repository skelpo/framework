<?php

namespace Skelpo\Framework\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class that represents a simple form. Purpose is to have some helper functions already defined.
 * Names of forms are always generated and not chosen.
 */
class FormType extends AbstractType
{
    protected $url;
	/**
	 * Returns the name of this form.
	 */
    public function getName()
    {
    	$n = get_class($this);
		// TODO: change it to dynamic values
		$n = substr($n,10,-4);
		$n = str_replace("\\","_",$n);
        return "Form_".$n;
    }
}
?>