<?php

namespace Skelpo\Framework\Routing;


class Router extends \Symfony\Bundle\FrameworkBundle\Routing\Router
{
	public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH, $defaultLocale = "en")
    {
    	if (substr($name,0,1)!="/") $name = "/".$name;
		if (isset($parameters['_locale']))
		{
			if (!stristr($name,"{_locale}") && $defaultLocale != $parameters['_locale'])
			{
				$name = "/{_locale}".$name;
			}
			else {
				unset($parameters['_locale']);
	        }
		}
		else {
			unset($parameters['_locale']);
        }
        return parent::generate($name, $parameters, $referenceType);
    }
}

?>