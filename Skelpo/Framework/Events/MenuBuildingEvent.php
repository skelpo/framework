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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Skelpo\Framework\View;

/**
 * Controller event class.
 * Carries the controller to the listener.
 */
class MenuBuildingEvent extends Event
{
	/**
	 * The menu we are carrying.
	 */
	protected $menu;
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
	public function __construct(Menu $m = null, Request $q = null, Response $r = null)
	{
		$this->menu = $m;
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
	public function getMenu()
	{
		return $this->menu;
	}

	/**
	 */
	public function setResponse(Response $q)
	{
		$this->response = $q;
	}
}