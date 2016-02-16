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

class JsShrinkCommand extends ContainerAwareCommand
{

	/**
	 *
	 * {@inheritdoc}
	 *
	 */
	protected function configure()
	{
		$this->setName('skelpo:js:shrink');
		$this->setDescription('Shrinks a folder with javascript.');
		$this->addArgument('path', InputArgument::REQUIRED, 'Where is the js located?');
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);
		$path = $this->getContainer()->getParameter('kernel.root_dir') . '/' . $input->getArgument('path') . "";
		
		if (! is_dir($path))
		{
			$io->error(sprintf('The given document directory "%s" does not exist', $path));
			
			return 1;
		}
		
		$io->success(sprintf('Found sources here here: %s', $path));
		$io->comment('Quit the process with CONTROL-C.');
		
		$files = scandir($path);
		foreach ($files as $file)
		{
			if (substr($file, - 3) == ".js")
			{
				$jsoutput = \JShrink\Minifier::minify(file_get_contents($path . '/' . $file), array(
						'flaggedComments' => false 
				));
				file_put_contents($path . '/' . $file, $jsoutput);
				$io->success(sprintf('Shrunk: ' . $path . '/' . $file));
			}
		}
		
		$io->success(sprintf('Success!'));
		
		$output->writeln($text);
	}
}
