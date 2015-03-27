<?php

namespace Skelpo\Framework\Events;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Skelpo\Framework\View;


class ControllerEvent extends Event
{
    private $controller;
	
    public function __construct(Controller $c)
    {
        $this->controller = $c;
    }
	
	public function getController()
	{
		return $this->controller;
	}

}