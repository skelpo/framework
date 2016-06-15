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
namespace Skelpo\Framework\Annotations\Router;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"ALL"})
 */
final class UrlParam extends Annotation
{
	public $name;
	public $type;
	public $default = "";

	/**
	 * Returns whether there is a default or not.
	 */
	public function isDefaultValueAvailable(): bool
	{
		if (is_null($this->default))
		{
			return false;
		}
		return true;
	}

	/**
	 * Returns the default.
	 */
	public function getDefaultValue(): string
	{
		return $this->default;
	}
}
