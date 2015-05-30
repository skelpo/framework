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
namespace Skelpo\Framework\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class that represents a simple form type.
 * Purpose is to have some helper functions already defined.
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
		$n = substr($n, 10, - 4);
		$n = str_replace("\\", "_", $n);
		return "Form_" . $n;
	}
}
?>