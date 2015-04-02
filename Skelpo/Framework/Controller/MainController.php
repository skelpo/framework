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
	 * Empty index action to create the routes and show templates.
	 */
    public function indexAction()
    {
    }
}
