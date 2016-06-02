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
namespace Skelpo\Framework\Model;

use Doctrine\Common\Annotations\AnnotationReader;

/**
 * Abstract model class.
 * What we do different to doctrine here is that we don't actually set all setters and getters
 * but we have function catching all "setXXX" and "getXXX" function names so that we replace it by our own setters
 * and getters.
 */
abstract class Model
{

	/**
	 * Sets variable $name to $value.
	 *
	 * @param string $name
	 * @param object $value
	 */
	protected function setVar($name, $value)
	{
		$this->$name = $value;
	}

	/**
	 * Returns variable $name.
	 *
	 * @param string $name Name of the var.
	 * @return object
	 */
	protected function getVar($name)
	{
		return $this->$name;
	}

	/**
	 * Sets variable $name to $value.
	 *
	 * @param string $name
	 * @param object $value
	 */
	public function __set($name, $value)
	{
		$this->$name = $value;
	}

	/**
	 * Returns variable $name.
	 *
	 * @param string $name
	 * @return object
	 */
	public function __get($name)
	{
		return $this->$name;
	}

	/**
	 * Takes all values from the array and assigns them to this model.
	 *
	 * @param string[] $values
	 */
	public function setWithArray($values)
	{
		foreach ($values as $k => $v)
		{
			$this->__set($k, $v);
		}
	}

	/**
	 * Returns an array containing all fields, that aren't objects, and their values.
	 *
	 * @param string[] $fields
	 * @return string[] The model in an array.
	 */
	public function getAsArray($fields = array())
	{
		$ret = array();
		if (count($fields) > 0)
			$vars = $fields;
		else
			$vars = $this->getKeys();
		
		$reader = new AnnotationReader();
		$reflect = new \ReflectionClass(get_class($this));
		$vars2 = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);
		foreach ($vars2 as $var)
		{
			$relation = $reader->getPropertyAnnotation($var, 'Skelpo\\Framework\\Annotations\\Model\\ArrayAccess');
			if ($relation)
			{
				if ($relation->allowed == false)
				{
					
					if (in_array($var->name, $vars))
					{
						foreach ($vars as $d => $v)
						{
							if ($v == $var->name)
							{
								unset($vars[$d]);
							}
						}
					}
				}
			}
		}
		foreach ($vars as $a)
		{
			$n = "get" . ucwords($a);
			$o = $this->$n();
			if (! is_object($o))
				$ret[$a] = $o;
		}
		return $ret;
	}

	/**
	 * Returns all keys of this model.
	 * Excludes the proxy keys of doctrine though.
	 *
	 * @return string[]
	 */
	public function getKeys()
	{
		$ret = array();
		
		$reflect = new \ReflectionClass($this);
		$vars = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);
		$staticVars = $reflect->getProperties(\ReflectionProperty::IS_STATIC);
		foreach ($vars as $a)
		{
			if (! in_array($a, $staticVars) && ! stristr($a->class, "__CG__"))
			{
				$ret[] = $a->getName();
			}
		}
		return $ret;
	}

	/**
	 * Formats this model as a string.
	 * Tries to look for the name and the title but if not found
	 * will return the model's class name and the id.
	 *
	 * @return string
	 */
	public function __toString()
	{
		$keys = $this->getKeys();
		if (in_array("name", $keys))
		{
			return $this->getName();
		}
		else if (in_array("title", $keys))
		{
			return $this->getTitle();
		}
		else
		{
			return "<" . get_class($this) . "> <" . $this->getId() . ">";
		}
	}

	/**
	 * Manipulates all requests for a generic function "getWHATEVER" and passes on the parameters
	 * so that we get and set all varialbes.
	 *
	 * @param string $method The method.
	 * @param string[] $arguments_ The arguments.
	 */
	public function __call($method, $arguments_)
	{
		$arguments = array();
		
		if (strlen($method) > 3)
		{
			$vO = substr($method, 0, 3);
			if ($vO == "set" || $vO == "get")
			{
				$v = substr($method, 3);
				$v1 = strtolower(substr($v, 0, 1)) . substr($v, 1);
				$arguments = array();
				
				$arguments[] = $v1;
				if (isset($arguments_[0]))
				{
					$arguments[] = $arguments_[0];
				}
				else
				{
					// fix to make sure null is set
					$arguments[] = null;
				}
				$method = $vO . "Var";
			}
		}
		return call_user_func_array(array(
				$this,
				$method 
		), $arguments);
	}
}
