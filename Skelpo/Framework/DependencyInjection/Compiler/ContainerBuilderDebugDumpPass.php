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
use Symfony\Component\DependencyInjection\Dumper\XmlDumper;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Dumps the ContainerBuilder to a cache file so that it can be used by
 * debugging tools such as the debug:container console command.
 *
 * @author Ryan Weaver <ryan@thatsquality.com>
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ContainerBuilderDebugDumpPass implements CompilerPassInterface
{

	public function process(ContainerBuilder $container)
	{
		$dumper = new XmlDumper($container);
		$filename = $container->getParameter('debug.container.dump');
		$filesystem = new Filesystem();
		$filesystem->dumpFile($filename, $dumper->dump(), null);
		try
		{
			$filesystem->chmod($filename, 0666, umask());
		}
		catch (IOException $e)
		{
			// discard chmod failure (some filesystem may not support it)
		}
	}
}
