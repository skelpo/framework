<?php

namespace Skelpo\Framework\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class BackendController extends MainController
{
    public function index2Action()
    {
    	//die("hier");
        return new Response("test 1 ralph");
    }
	
}
