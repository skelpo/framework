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

use Symfony\Component\Form\FormRendererEngineInterface;
use Skelpo\Framework\Framework;

/**
 * The interface to ensure we get the frame.
 */
interface SmartyRendererEngineInterface extends FormRendererEngineInterface
{

	/**
	 * Framework has to be set.
	 *
	 * @param Skelpo\Framework\Framework $s The framework.
	 */
	public function setFramework(Framework $s);
}
