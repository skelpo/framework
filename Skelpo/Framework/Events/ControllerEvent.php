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
namespace Skelpo\Framework\Events;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Skelpo\Framework\View;

/**
 * Controller event class.
 * Carries the controller to the listener.
 */
class ControllerEvent extends Event
{
	/**
	 * The controller we are carrying.
	 *
	 * @var Symfony\Bundle\FrameworkBundle\Controller\Controller
	 */
	protected $controller;
	/**
	 * The request.
	 *
	 * @var Symfony\Component\HttpFoundation\Request
	 */
	protected $request;
	/**
	 * The response.
	 *
	 * @var Symfony\Component\HttpFoundation\Response
	 */
	protected $response;

	/**
	 * Creates a new event with controller $c.
	 *
	 * @param Symfony\Bundle\FrameworkBundle\Controller\Controller $c The controller for this event.
	 * @param Symfony\Component\HttpFoundation\Request $q The request.
	 * @param Symfony\Component\HttpFoundation\Response $r The response.
	 */
	public function __construct(Controller $c = null, Request $q = null, Response $r = null)
	{
		$this->controller = $c;
		$this->request = $q;
		$this->response = $r;
	}

	/**
	 * Returns the request object.
	 *
	 * @return Symfony\Component\HttpFoundation\Request
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * Returns the response object.
	 *
	 * @return Symfony\Component\HttpFoundation\Response
	 */
	public function getResponse()
	{
		return $this->response;
	}

	/**
	 * Returns the controller.
	 *
	 * @return Symfony\Bundle\FrameworkBundle\Controller\Controller
	 */
	public function getController()
	{
		return $this->controller;
	}

	/**
	 * Sets the response.
	 *
	 * @param Response $q
	 */
	public function setResponse(Response $q)
	{
		$this->response = $q;
	}
}