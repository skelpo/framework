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
 * Adds tagged translation.extractor services to translation extractor.
 */
class TranslationExtractorPass implements CompilerPassInterface
{

	public function process(ContainerBuilder $container)
	{
		if (! $container->hasDefinition('translation.extractor'))
		{
			return;
		}
		
		$definition = $container->getDefinition('translation.extractor');
		
		foreach ($container->findTaggedServiceIds('translation.extractor') as $id => $attributes)
		{
			if (! isset($attributes[0]['alias']))
			{
				throw new \RuntimeException(sprintf('The alias for the tag "translation.extractor" of service "%s" must be set.', $id));
			}
			
			$definition->addMethodCall('addExtractor', array(
					$attributes[0]['alias'],
					new Reference($id) 
			));
		}
	}
}
