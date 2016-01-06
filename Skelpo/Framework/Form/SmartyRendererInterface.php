<?php

/**
 * This file is part of the skelpo framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @version 1.0.0
 * @author Ralph Kuepper <ralph.kuepper@skelpo.com>
 * @copyright 2016 Skelpo Inc. www.skelpo.com
 */
namespace Skelpo\Framework\Form;

use Symfony\Component\Form\FormRendererInterface;
use Skelpo\Framework\Framework;

/**
 * Interface to ensure we get the framework.
 */
interface SmartyRendererInterface extends FormRendererInterface
{

	/**
	 * Framework has to be given.
	 *
	 * @param Framework $s The framework.
	 */
	public function setFramework(Framework $s);
}
