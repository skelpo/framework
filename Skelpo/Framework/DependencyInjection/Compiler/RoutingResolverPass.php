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

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds tagged routing.loader services to routing.resolver service.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class RoutingResolverPass implements CompilerPassInterface
{

	public function process(ContainerBuilder $container)
	{
		if (false === $container->hasDefinition('routing.resolver'))
		{
			return;
		}
		
		$definition = $container->getDefinition('routing.resolver');
		
		foreach ($container->findTaggedServiceIds('routing.loader') as $id => $attributes)
		{
			$definition->addMethodCall('addLoader', array(
					new Reference($id) 
			));
		}
	}
}
