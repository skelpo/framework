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
use Symfony\Component\DependencyInjection\Reference;

/**
 * Adds all services with the tags "serializer.encoder" and "serializer.normalizer" as
 * encoders and normalizers to the Serializer service.
 *
 * @author Javier Lopez <f12loalf@gmail.com>
 */
class SerializerPass implements CompilerPassInterface
{

	public function process(ContainerBuilder $container)
	{
		if (! $container->hasDefinition('serializer'))
		{
			return;
		}
		
		// Looks for all the services tagged "serializer.normalizer" and adds them to the Serializer service
		$normalizers = $this->findAndSortTaggedServices('serializer.normalizer', $container);
		$container->getDefinition('serializer')->replaceArgument(0, $normalizers);
		
		// Looks for all the services tagged "serializer.encoders" and adds them to the Serializer service
		$encoders = $this->findAndSortTaggedServices('serializer.encoder', $container);
		$container->getDefinition('serializer')->replaceArgument(1, $encoders);
	}

	/**
	 * Finds all services with the given tag name and order them by their priority.
	 *
	 * @param string $tagName
	 * @param ContainerBuilder $container
	 *
	 * @return array
	 *
	 * @throws \RuntimeException
	 */
	private function findAndSortTaggedServices($tagName, ContainerBuilder $container)
	{
		$services = $container->findTaggedServiceIds($tagName);
		
		if (empty($services))
		{
			throw new \RuntimeException(sprintf('You must tag at least one service as "%s" to use the Serializer service', $tagName));
		}
		
		$sortedServices = array();
		foreach ($services as $serviceId => $tags)
		{
			foreach ($tags as $attributes)
			{
				$priority = isset($attributes['priority']) ? $attributes['priority'] : 0;
				$sortedServices[$priority][] = new Reference($serviceId);
			}
		}
		
		krsort($sortedServices);
		
		// Flatten the array
		return call_user_func_array('array_merge', $sortedServices);
	}
}