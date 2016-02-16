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

class TranslatorPass implements CompilerPassInterface
{

	public function process(ContainerBuilder $container)
	{
		if (! $container->hasDefinition('translator.default'))
		{
			return;
		}
		
		$loaders = array();
		foreach ($container->findTaggedServiceIds('translation.loader') as $id => $attributes)
		{
			$loaders[$id][] = $attributes[0]['alias'];
			if (isset($attributes[0]['legacy-alias']))
			{
				$loaders[$id][] = $attributes[0]['legacy-alias'];
			}
		}
		
		if ($container->hasDefinition('translation.loader'))
		{
			$definition = $container->getDefinition('translation.loader');
			foreach ($loaders as $id => $formats)
			{
				foreach ($formats as $format)
				{
					$definition->addMethodCall('addLoader', array(
							$format,
							new Reference($id) 
					));
				}
			}
		}
		
		$container->findDefinition('translator.default')->replaceArgument(2, $loaders);
	}
}
