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
 * Registers the cache warmers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class AddCacheWarmerPass implements CompilerPassInterface
{

	/**
	 *
	 * {@inheritdoc}
	 *
	 */
	public function process(ContainerBuilder $container)
	{
		if (! $container->hasDefinition('cache_warmer'))
		{
			return;
		}
		
		$warmers = array();
		foreach ($container->findTaggedServiceIds('kernel.cache_warmer') as $id => $attributes)
		{
			$priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
			$warmers[$priority][] = new Reference($id);
		}
		
		if (empty($warmers))
		{
			return;
		}
		
		// sort by priority and flatten
		krsort($warmers);
		$warmers = call_user_func_array('array_merge', $warmers);
		
		$container->getDefinition('cache_warmer')->replaceArgument(0, $warmers);
	}
}
