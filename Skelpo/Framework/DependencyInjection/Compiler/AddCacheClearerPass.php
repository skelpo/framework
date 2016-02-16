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
 * Registers the cache clearers.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class AddCacheClearerPass implements CompilerPassInterface
{

	/**
	 *
	 * {@inheritdoc}
	 *
	 */
	public function process(ContainerBuilder $container)
	{
		if (! $container->hasDefinition('cache_clearer'))
		{
			return;
		}
		
		$clearers = array();
		foreach ($container->findTaggedServiceIds('kernel.cache_clearer') as $id => $attributes)
		{
			$clearers[] = new Reference($id);
		}
		
		$container->getDefinition('cache_clearer')->replaceArgument(0, $clearers);
	}
}
