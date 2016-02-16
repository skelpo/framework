<?php

/**
 * This file is part of the skelpo framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @version 1.0.0
 * @author Ralph Kuepper <ralph.kuepper@skelpo.com>
 * @copyright 2016 Skelpo Inc. www.skelpo.com
 */
namespace Skelpo\Framework\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class TsBuildCommand extends ContainerAwareCommand
{

	/**
	 *
	 * {@inheritdoc}
	 *
	 */
	protected function configure()
	{
		$this->setName('skelpo:ts:build');
		$this->setDescription('Compiles a typescript app (like angularjs 2) and puts the output together.');
		$this->addArgument('path', InputArgument::REQUIRED, 'Where is the app located?');
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);
		$path = $this->getContainer()->getParameter('kernel.root_dir') . '/' . $input->getArgument('path');
		
		if (! is_dir($path))
		{
			$io->error(sprintf('The given document directory "%s" does not exist', $path));
			
			return 1;
		}
		
		$io->success(sprintf('Found app here: %s', $path));
		$io->comment('Quit the process with CONTROL-C.');
		
		$process = new Process('cd ' . $path . ' && npm run tsc');
		try
		{
			$process->mustRun();
			
			$output->writeln($process->getOutput());
		}
		catch (ProcessFailedException $e)
		{
			$io->error($e->getMessage());
			return 1;
		}
		
		$io->success(sprintf('Successfully compiled the app.'));
		
		$output->writeln($text);
	}
}
