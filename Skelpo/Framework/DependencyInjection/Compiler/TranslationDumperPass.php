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
 * Adds tagged translation.formatter services to translation writer.
 */
class TranslationDumperPass implements CompilerPassInterface
{

	public function process(ContainerBuilder $container)
	{
		if (! $container->hasDefinition('translation.writer'))
		{
			return;
		}
		
		$definition = $container->getDefinition('translation.writer');
		
		foreach ($container->findTaggedServiceIds('translation.dumper') as $id => $attributes)
		{
			$definition->addMethodCall('addDumper', array(
					$attributes[0]['alias'],
					new Reference($id) 
			));
		}
	}
}
