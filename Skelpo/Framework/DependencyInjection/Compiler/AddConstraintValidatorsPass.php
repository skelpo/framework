<?php

/**
 * This file is part of the skelpo framework.
 * This file has been partially or fully taken
 * from the symfony framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @version 1.1.0
 * @author Ralph Kuepper <ralph.kuepper@skelpo.com>
 * @author symfony Team
 * @copyright 2016 Skelpo Inc. www.skelpo.com
 */
namespace Skelpo\Framework\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class AddConstraintValidatorsPass implements CompilerPassInterface
{

	public function process(ContainerBuilder $container)
	{
		if (! $container->hasDefinition('validator.validator_factory'))
		{
			return;
		}
		
		$validators = array();
		foreach ($container->findTaggedServiceIds('validator.constraint_validator') as $id => $attributes)
		{
			if (isset($attributes[0]['alias']))
			{
				$validators[$attributes[0]['alias']] = $id;
			}
		}
		
		$container->getDefinition('validator.validator_factory')->replaceArgument(1, $validators);
	}
}
