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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * AddConsoleCommandPass.
 *
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class AddConsoleCommandPass implements CompilerPassInterface
{

	public function process(ContainerBuilder $container)
	{
		$commandServices = $container->findTaggedServiceIds('console.command');
		
		foreach ($commandServices as $id => $tags)
		{
			$definition = $container->getDefinition($id);
			
			if (! $definition->isPublic())
			{
				throw new \InvalidArgumentException(sprintf('The service "%s" tagged "console.command" must be public.', $id));
			}
			
			if ($definition->isAbstract())
			{
				throw new \InvalidArgumentException(sprintf('The service "%s" tagged "console.command" must not be abstract.', $id));
			}
			
			$class = $container->getParameterBag()->resolveValue($definition->getClass());
			if (! is_subclass_of($class, 'Symfony\\Component\\Console\\Command\\Command'))
			{
				throw new \InvalidArgumentException(sprintf('The service "%s" tagged "console.command" must be a subclass of "Symfony\\Component\\Console\\Command\\Command".', $id));
			}
			$container->setAlias('console.command.' . strtolower(str_replace('\\', '_', $class)), $id);
		}
		
		$container->setParameter('console.command.ids', array_keys($commandServices));
	}
}
