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
namespace Skelpo\Framework\Routing;

/**
 * Router class that fixes our urls.
 * Locale is usually always included.
 */
class Router extends \Symfony\Bundle\FrameworkBundle\Routing\Router
{

	/**
	 * Returns the generated url.
	 *
	 * In addition to the normal router we add the locale as well.
	 *
	 * @param string $name Name of the route.
	 * @param string[] $parameters Parameters for the route.
	 * @param string $referenceType Absolute or relevate path?
	 * @param string $defaultLocale What is the default locale?
	 * @return string
	 */
	public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH, $defaultLocale = "en")
	{
		if (substr($name, 0, 1) != "/")
			$name = "/" . $name;
		if (isset($parameters['_locale']))
		{
			if (! stristr($name, "{_locale}") && $defaultLocale != $parameters['_locale'])
			{
				$name = "/{_locale}" . $name;
			}
			else
			{
				unset($parameters['_locale']);
			}
		}
		else
		{
			unset($parameters['_locale']);
		}
		return parent::generate($name, $parameters, $referenceType);
	}
}

?>