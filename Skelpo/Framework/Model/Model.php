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
	
	public function __set($name, $value) 
    {
        $this->$name = $value;
    }

    public function __get($name) 
    {
    	return $this->$name;
    }
	
	/**
	 * Manipulates all requests for a generic function "getWHATEVER" and passes on the parameters
	 * so that we get and set all varialbes.
	 */
	public function __call($method, $arguments_) {
		if (strlen($method)>3)
		{
			$vO = substr($method,0,3);
			if ($vO=="set" || $vO=="get")
			{
				$v = substr($method,3);
				$v1 = strtolower(substr($v,0,1)).substr($v,1);
				$arguments = array();
				$arguments[] = $v1;
				if (isset($arguments_[0]))
				{
					$arguments[] = $arguments_[0];
				
				}
				$method = $vO."Var";
				
			}
			
		}
		return call_user_func_array(array($this,$method), $arguments);
	}
}
