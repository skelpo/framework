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
namespace Skelpo\Framework\Events;

use Symfony\Component\EventDispatcher\Event;
use Skelpo\Framework\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Skelpo\Framework\View\View;

/**
 * Controller event class.
 * Carries the controller to the listener.
 */
class ControllerEvent extends Event
{
	/**
	 * The controller we are carrying.
	 *
	 * @var Skelpo\Framework\Controller\Controller
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
	 * The view.
	 *
	 * @var Skelpo\Framework\View\View
	 */
	protected $view;

	/**
	 * Creates a new event with controller $c.
	 *
	 * @param Skelpo\Framework\Controller\Controller $c The controller for this event.
	 * @param Symfony\Component\HttpFoundation\Request $q The request.
	 * @param Symfony\Component\HttpFoundation\Response $r The response.
	 */
	public function __construct(Controller $c = null, Request $q = null, Response $r = null, View $v = null)
	{
		$this->controller = $c;
		$this->request = $q;
		$this->response = $r;
		$this->view = $v;
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
	 *
	 * @return the $view
	 */
	public function getView()
	{
		return $this->view;
	}

	/**
	 * Sets the view.
	 *
	 * @param \Skelpo\Framework\View $view
	 */
	public function setView($view)
	{
		$this->view = $view;
	}

	/**
	 * Returns the controller.
	 *
	 * @return Skelpo\Framework\Controller\Controller
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