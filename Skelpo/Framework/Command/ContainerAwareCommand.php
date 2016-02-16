<?php

/**
 * This file is part of the skelpo framework.
 * This file has been partially or fully taken
 * from the symfony framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @version 1.0.0
 * @author Ralph Kuepper <ralph.kuepper@skelpo.com>
 * @author symfony Team
 * @copyright 2016 Skelpo Inc. www.skelpo.com
 */
namespace Skelpo\Framework\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Command.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class ContainerAwareCommand extends Command implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 *
	 * @return ContainerInterface
	 *
	 * @throws \LogicException
	 */
	protected function getContainer()
	{
		if (null === $this->container)
		{
			$application = $this->getApplication();
			if (null === $application)
			{
				throw new \LogicException('The container cannot be retrieved as the application instance is not yet set.');
			}
			
			$this->container = $application->getKernel()->getContainer();
		}
		
		return $this->container;
	}
}
