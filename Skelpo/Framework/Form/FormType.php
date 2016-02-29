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
namespace Skelpo\Framework\Form;

use Symfony\Component\Form\AbstractType;

/**
 * Class that represents a simple form type.
 * Purpose is to have some helper functions already defined.
 * Names of forms are always generated and not chosen.
 */
class FormType extends AbstractType
{
	/**
	 *
	 * @var string The url of this form.
	 */
	protected $url;

	/**
	 * Returns the name of this form.
	 */
	public function getName()
	{
		$n = get_class($this);
		$n = substr($n, 10, - 4);
		$n = str_replace("\\", "_", $n);
		return "Form_" . $n;
	}
}
