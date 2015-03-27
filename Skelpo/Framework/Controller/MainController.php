<?php

namespace Skelpo\Framework\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class MainController extends Controller
{
    
    public function indexAction()
    {
    	//die("hier");
        return new Response("test");
    }
	public function render($view, array $parameters = array(), Response $response = null)
	{
		
	}
	public function backendAction()
    {
    	//die("hier");
        return new Response("test");
    }
}
