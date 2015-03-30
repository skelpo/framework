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

namespace Skelpo\Framework\Model;

/**
 * Abstract model class. What we do different to doctrine here is that we don't actually set all setters and getters
 * but we have function catching all "setXXX" and "getXXX" function names so that we replace it by our own setters
 * and getters.
 */
abstract class Model 
{
	/**
	 * Sets variable $name to $value.
	 */
	protected function setVar($name, $value)
	{
		$this->$name = $value;
	}
	/**
	 * Returns variable $name.
	 */
	protected function getVar($name)
	{
		return $this->$name;
	}
	/**
	 * Manipulates all requests for a generic function "getWHATEVER" and passes on the parameters
	 * so that we get and set all varialbes.
	 */
	public function __call($method, $arguments) {
		if (strlen($method)>3)
		{
			$v = substr($method,3);
			if ($v=="set" || $v=="get")
			{
				$v1 = strtolower(substr($v,0,1)).substr($v,1);
				$v2 = $arguments[0];
				$arguments = array();
				$arguments[] = $v1;
				$arguments[] = $v2;
				$method = $v."Var";
			}
			
		}
		return call_user_func_array($this->{$method}, $arguments);
	}
}
