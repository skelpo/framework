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

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class CompilerDebugDumpPass implements CompilerPassInterface
{

	public function process(ContainerBuilder $container)
	{
		$filename = self::getCompilerLogFilename($container);
		
		$filesystem = new Filesystem();
		$filesystem->dumpFile($filename, implode("\n", $container->getCompiler()->getLog()), null);
		try
		{
			$filesystem->chmod($filename, 0666, umask());
		}
		catch (IOException $e)
		{
			// discard chmod failure (some filesystem may not support it)
		}
	}

	public static function getCompilerLogFilename(ContainerInterface $container)
	{
		$class = $container->getParameter('kernel.container_class');
		
		return $container->getParameter('kernel.cache_dir') . '/' . $class . 'Compiler.log';
	}
}
