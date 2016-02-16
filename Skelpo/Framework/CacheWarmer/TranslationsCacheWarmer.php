<?php

/**
 * This file is part of the skelpo framework.
 * This file has been
 * partially or fully taken from the symfony framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @version 1.0.0
 * @author Ralph Kuepper <ralph.kuepper@skelpo.com>
 * @author symfony Team
 * @copyright 2016 Skelpo Inc. www.skelpo.com
 */
namespace Skelpo\Framework\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Generates the catalogues for translations.
 *
 * @author Xavier Leune <xavier.leune@gmail.com>
 */
class TranslationsCacheWarmer implements CacheWarmerInterface
{
	private $translator;

	public function __construct(TranslatorInterface $translator)
	{
		$this->translator = $translator;
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 */
	public function warmUp($cacheDir)
	{
		if ($this->translator instanceof WarmableInterface)
		{
			$this->translator->warmUp($cacheDir);
		}
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 */
	public function isOptional()
	{
		return true;
	}
}
