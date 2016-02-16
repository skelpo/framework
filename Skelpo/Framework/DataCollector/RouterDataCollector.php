<?php

/**
 * This file is part of the skelpo framework.
 * This file has been partially or fully taken
 * from the symfony framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @version 1.0.0
 * @author Ralph Kuepper <ralph.kuepper@skelpo.com>
 * @author symfony Team
 * @copyright 2016 Skelpo Inc. www.skelpo.com
 */
namespace Skelpo\Framework\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\RouterDataCollector as BaseRouterDataCollector;
use Symfony\Component\HttpFoundation\Request;
// use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;

/**
 * RouterDataCollector.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class RouterDataCollector extends BaseRouterDataCollector
{

	public function guessRoute(Request $request, $controller)
	{
		if (is_array($controller))
		{
			$controller = $controller[0];
		}
		
		/*
		 * if ($controller instanceof RedirectController)
		 * {
		 * return $request->attributes->get('_route');
		 * }
		 */
		
		return parent::guessRoute($request, $controller);
	}
}
