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

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Skelpo\Framework\Framework;

/**
 * Parent class for all controllers.
 */
abstract class Controller extends BaseController
{

	/**
	 * Init functions that matter "before" everything else.
	 */
	public function init()
	{
		// intentially empty
	}

	/**
	 * Returns our View.
	 *
	 * @return Skelpo\Framework\View\View
	 */
	protected function getView()
	{
		return $this->get('view');
	}

	/**
	 * Returns the current language.
	 *
	 * @return Skelpo\Framework\Language\Language
	 */
	protected function getLanguage()
	{
		return $this->getView()->getLanguage();
	}

	/**
	 * Returns our framework.
	 *
	 * @return Skelpo\Framework\Framework
	 */
	protected function getFramework()
	{
		return $this->get('framework');
	}

	/**
	 * Redirects to a certain route.
	 * Checks for beginning "/".
	 *
	 * @param string $route The route to direct to.
	 * @param string[] $parameters Parameters for this route
	 * @param int $status The status for this redirect.
	 * @return Symfony\Component\HttpFoundation\Response The response redirecting us.
	 */
	protected function redirectToRoute($route, array $parameters = array(), $status = 302)
	{
		if (substr($route, 0, 1) != "/")
			$route = "/" . $route;
		return $this->redirect($this->generateUrl($route, $parameters), $status);
	}
}
