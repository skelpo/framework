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
 
namespace Skelpo\Framework\Events;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Skelpo\Framework\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller event class. Carries the controller to the listener.
 */
class ControllerEvent extends Event
{
	/**
	 * The controller we are carrying.
	 */
    protected $controller;
	/**
	 * The request.
	 */
	protected $request;
	/** 
	 * The response.
	 */
	protected $response;
	/**
	 * Creates a new event with controller $c.
	 */
    public function __construct(Controller $c = null, Request $q = null, Response $r = null)
    {
        $this->controller = $c;
		$this->request = $q;
		$this->response = $r;
    }
	/**
	 * Returns the request object.
	 */
	public function getRequest()
	{
		return $this->request;
	}
	/**
	 * Returns the response object.
	 */
	public function getResponse()
	{
		return $this->response;
	}
	/**
	 * Returns the controller.
	 */
	public function getController()
	{
		return $this->controller;
	}
	/**
	 * 
	 */
	public function setResponse(Response $q)
	{
		$this->response = $q;
	}

}