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
namespace Skelpo\Framework\Translation;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Loader\LoaderInterface;

/**
 * TranslationLoader loads translation messages from translation files.
 *
 * @author Michel Salib <michelsalib@hotmail.com>
 */
class TranslationLoader
{
	/**
	 * Loaders used for import.
	 *
	 * @var array
	 */
	private $loaders = array();

	/**
	 * Adds a loader to the translation extractor.
	 *
	 * @param string $format The format of the loader
	 * @param LoaderInterface $loader
	 */
	public function addLoader($format, LoaderInterface $loader)
	{
		$this->loaders[$format] = $loader;
	}

	/**
	 * Loads translation messages from a directory to the catalogue.
	 *
	 * @param string $directory the directory to look into
	 * @param MessageCatalogue $catalogue the catalogue
	 */
	public function loadMessages($directory, MessageCatalogue $catalogue)
	{
		if (! is_dir($directory))
		{
			return;
		}
		
		foreach ($this->loaders as $format => $loader)
		{
			// load any existing translation files
			$finder = new Finder();
			$extension = $catalogue->getLocale() . '.' . $format;
			$files = $finder->files()->name('*.' . $extension)->in($directory);
			foreach ($files as $file)
			{
				$domain = substr($file->getFileName(), 0, - 1 * strlen($extension) - 1);
				$catalogue->addCatalogue($loader->load($file->getPathname(), $catalogue->getLocale(), $domain));
			}
		}
	}
}
