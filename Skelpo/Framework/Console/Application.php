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
namespace Skelpo\Framework\Console;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Application.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Application extends BaseApplication
{
	private $kernel;
	private $commandsRegistered = false;

	/**
	 * Constructor.
	 *
	 * @param KernelInterface $kernel A KernelInterface instance
	 */
	public function __construct(KernelInterface $kernel)
	{
		$this->kernel = $kernel;
		
		parent::__construct('Symfony', Kernel::VERSION . ' - ' . $kernel->getName() . '/' . $kernel->getEnvironment() . ($kernel->isDebug() ? '/debug' : ''));
		
		$this->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', $kernel->getEnvironment()));
		$this->getDefinition()->addOption(new InputOption('--no-debug', null, InputOption::VALUE_NONE, 'Switches off debug mode.'));
	}

	/**
	 * Gets the Kernel associated with this Console.
	 *
	 * @return KernelInterface A KernelInterface instance
	 */
	public function getKernel()
	{
		return $this->kernel;
	}

	/**
	 * Runs the current application.
	 *
	 * @param InputInterface $input An Input instance
	 * @param OutputInterface $output An Output instance
	 *       
	 * @return int 0 if everything went fine, or an error code
	 */
	public function doRun(InputInterface $input, OutputInterface $output)
	{
		$this->kernel->boot();
		
		if (! $this->commandsRegistered)
		{
			$this->registerCommands();
			
			$this->commandsRegistered = true;
		}
		
		$container = $this->kernel->getContainer();
		
		foreach ($this->all() as $command)
		{
			if ($command instanceof ContainerAwareInterface)
			{
				$command->setContainer($container);
			}
		}
		
		$this->setDispatcher($container->get('event_dispatcher'));
		
		return parent::doRun($input, $output);
	}

	protected function registerCommands()
	{
		$container = $this->kernel->getContainer();
		
		foreach ($this->kernel->getBundles() as $bundle)
		{
			if ($bundle instanceof Bundle)
			{
				$bundle->registerCommands($this);
			}
		}
		
		if ($container->hasParameter('console.command.ids'))
		{
			foreach ($container->getParameter('console.command.ids') as $id)
			{
				$this->add($container->get($id));
			}
		}
	}
}
