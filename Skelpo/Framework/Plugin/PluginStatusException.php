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
namespace Skelpo\Framework\Plugin;

/**
 * This exception is thrown if a plugin has errors.
 * For example
 * a plugin could not find its models or similar things.
 */
class PluginStatusException extends \Exception
{
}

?>