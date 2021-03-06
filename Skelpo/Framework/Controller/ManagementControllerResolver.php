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
namespace Skelpo\Framework\Controller;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Doctrine\Common\Annotations\AnnotationReader;

/**
 * This class is managing all controllers.
 * The primary difference to the
 * smarty controller is that this controller "knows" all controllers at all times.
 * Symfony is just loading the controller that is needed, we know and load
 * all of them to efficiently use them.
 */
class ManagementControllerResolver implements ControllerResolverInterface, ArgumentResolverInterface
{
	/**
	 * Our container.
	 *
	 * @var Symfony\Component\DependencyInjection\ContainerInterface
	 */
	protected $container;
	/**
	 * All controllers that are known to the system.
	 * This includes controllers from plugins.
	 *
	 * @var Skelpo\Framework\Controller\Controller[]
	 */
	protected $controllers;

	/**
	 * Constructor.
	 *
	 * @param Symfony\Component\DependencyInjection\ContainerInterface $container The container for this management class.
	 */
	public function __construct($container = null)
	{
		$this->container = $container;
		$this->controllers = array();
	}

	/**
	 * This method looks for a '_controller' request attribute that represents
	 * the controller name (a string like ClassName::MethodName).
	 *
	 * @param Symfony\Component\HttpFoundation\Request $request The request we are handeling.
	 * @throws InvalidArgumentException If the controller is not callable.
	 * @return Symfony\Bundle\FrameworkBundle\Controller\Controller The controller.
	 */
	public function getController(Request $request)
	{
		if (! $controller = $request->attributes->get('_controller'))
		{

			return false;
		}

		if (is_array($controller))
		{
			return $controller;
		}

		if (is_object($controller))
		{
			if (method_exists($controller, '__invoke'))
			{
				return $controller;
			}

			throw new \InvalidArgumentException(sprintf('Controller "%s" for URI "%s" is not callable.', get_class($controller), $request->getPathInfo()));
		}

		if (false === strpos($controller, ':'))
		{
			if (method_exists($controller, '__invoke'))
			{
				return $this->instantiateController($controller);
			}
			elseif (function_exists($controller))
			{
				return $controller;
			}
		}

		$callable = $this->createController($controller);

		if (! is_callable($callable))
		{
			throw new \InvalidArgumentException(sprintf('Controller "%s" for URI "%s" is not callable.', $controller, $request->getPathInfo()));
		}

		return $callable;
	}

	/**
	 * Returns the arguments of a request.
	 *
	 * @param Symfony\Component\HttpFoundation\Request $request
	 * @param Symfony\Bundle\FrameworkBundle\Controller\Controller The controller.
	 * @return string[]
	 */
	public function getArguments(Request $request, $controller)
	{
		$reflMthd = null;
		$reflObj = null;
		if (is_array($controller))
		{
			$reflMthd = new \ReflectionMethod($controller[0], $controller[1]);
			$reflObj = new \ReflectionObject($controller[0]);
		}
		elseif (is_object($controller) && ! $controller instanceof \Closure)
		{
			$reflObj = new \ReflectionObject($controller);
			$reflMthd = $reflObj->getMethod('__invoke');
		}
		else
		{
			$reflObj = null;
			$reflMthd = new \ReflectionFunction($controller);
		}
		$methodRelations = array();
		if ($reflMthd !== null)
		{
			$reader = new AnnotationReader();
			$methodRelations = $reader->getMethodAnnotations($reflMthd, 'Skelpo\\Framework\\Annotations\\Router\\UrlParam');
			foreach ($methodRelations as $key => $v)
			{
				$methodRelations[$v->name] = $v;
				unset($methodRelations[$key]);
			}
		}
		$finalParameters = array();
		$realParameters = $reflMthd->getParameters();
		foreach ($realParameters as $p)
		{
			if (isset($methodRelations[$p->name]))
			{
				$finalParameters[] = $methodRelations[$p->name];
			}
			else
			{
				$finalParameters[] = $p;
			}
		}

		return $this->doGetArguments($request, $controller, $finalParameters);
	}

	/**
	 * Checks for all the get arguments.
	 *
	 * @param Symfony\Component\HttpFoundation\Request $request
	 * @param Symfony\Bundle\FrameworkBundle\Controller\Controller $controller The controller.
	 * @param string[] $parameters All parameters.
	 * @throws \RuntimeException If an argument is not given
	 * @return string[]
	 */
	protected function doGetArguments(Request $request, $controller, array $parameters)
	{
		$attributes = $request->attributes->all();
		$arguments = array();
		foreach ($parameters as $param)
		{
			if (array_key_exists($param->name, $attributes))
			{
				if ($param->type == "string")
				{
					$arguments[] = $attributes[$param->name];
				}
				else if ($param->type == "int")
				{
					$arguments[] = intval($attributes[$param->name]);
				}
			}
			elseif ($param->isDefaultValueAvailable())
			{
				$arguments[] = $param->getDefaultValue();
			}
			elseif ($param->getClass() && $param->getClass()->isInstance($request))
			{
				$arguments[] = $request;
			}
			else
			{
				if (is_array($controller))
				{
					$repr = sprintf('%s::%s()', get_class($controller[0]), $controller[1]);
				}
				elseif (is_object($controller))
				{
					$repr = get_class($controller);
				}
				else
				{
					$repr = $controller;
				}

				throw new \RuntimeException(sprintf('Controller "%s" requires that you provide a value for the "$%s" argument (because there is no default value or because there is a non optional argument after this one).', $repr, $param->name));
			}
		}

		return $arguments;
	}

	/**
	 * Returns a callable for the given controller.
	 *
	 * @param string $controller A Controller string
	 *
	 * @return mixed A PHP callable
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function createController($controller)
	{
		if (false === strpos($controller, '::'))
		{
			throw new \InvalidArgumentException(sprintf('Unable to find controller "%s".', $controller));
		}

		list($class, $method) = explode('::', $controller, 2);

		if (! class_exists($class))
		{
			throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
		}

		return array(
				$this->instantiateController($class),
				$method
		);
	}

	/**
	 * Returns an instantiated controller
	 *
	 * @param string $class A class name
	 *
	 * @return object
	 */
	protected function instantiateController($class)
	{
		$k = md5($class);
		if (array_key_exists($k, $this->controllers))
		{
			return $this->controllers[$k];
		}
		else
		{
			$controller = new $class();
			if ($controller instanceof ContainerAwareInterface)
			{
				$controller->setContainer($this->container);
			}
			$this->controllers[$k] = $controller;
			return $controller;
		}
	}
}
