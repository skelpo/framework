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
 
namespace Skelpo\Framework\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Parent class for all controllers.
 */
abstract class MainController extends Controller
{
	/**
	 * Returns our smarty view.
	 */
	protected function getView()
	{
		return $this->get('view');
	}
   
	/**
	 * Redirects to a certain route. Checks for beginning "/".
	 */
	protected function redirectToRoute($route, array $parameters = array(), $status = 302)
    {
    	if (substr($route,0,1)!="/") $route = "/".$route;
        return $this->redirect($this->generateUrl($route, $parameters), $status);
    }
}
