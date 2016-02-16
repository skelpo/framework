<?php

/**
 * This file is part of the skelpo framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @version 1.1.0
 * @author Ralph Kuepper <ralph.kuepper@skelpo.com>
 * @copyright 2016 Skelpo Inc. www.skelpo.com
 */
namespace Skelpo\Framework;

use Skelpo\Framework\DependencyInjection\Compiler\AddCacheClearerPass;
use Skelpo\Framework\DependencyInjection\Compiler\AddCacheWarmerPass;
use Skelpo\Framework\DependencyInjection\Compiler\AddConstraintValidatorsPass;
use Skelpo\Framework\DependencyInjection\Compiler\AddValidatorInitializersPass;
use Skelpo\Framework\DependencyInjection\Compiler\CompilerDebugDumpPass;
use Skelpo\Framework\DependencyInjection\Compiler\ConfigCachePass;
use Skelpo\Framework\DependencyInjection\Compiler\ContainerBuilderDebugDumpPass;
use Skelpo\Framework\DependencyInjection\Compiler\LoggingTranslatorPass;
use Skelpo\Framework\DependencyInjection\Compiler\ProfilerPass;
use Skelpo\Framework\DependencyInjection\Compiler\PropertyInfoPass;
use Skelpo\Framework\DependencyInjection\Compiler\AddConsoleCommandPass;
use Skelpo\Framework\DependencyInjection\Compiler\RoutingResolverPass;
use Skelpo\Framework\DependencyInjection\Compiler\SerializerPass;
use Skelpo\Framework\DependencyInjection\Compiler\TranslationDumperPass;
use Skelpo\Framework\DependencyInjection\Compiler\TranslationExtractorPass;
use Skelpo\Framework\DependencyInjection\Compiler\TranslatorPass;
use Skelpo\Framework\DependencyInjection\Compiler\UnusedTagsPass;
use Skelpo\Framework\Module;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\DependencyInjection\FragmentRendererPass;

/**
 * Framework bundle to handle all our stuff.
 * This class is closely connected
 * to the Kernel class. The big difference between the two classes is that the
 * framework class is entirely the framework, without any influence from outsite,
 * while the Kernel class is the core of the application and can be changed in many ways.
 */
class Framework extends Bundle
{
	/**
	 * Our event dispatcher to deal with events.
	 *
	 * @var Symfony\Component\EventDispatcher\EventDispatcher
	 */
	private $eventDispatcher;
	
	/**
	 * The environment we are using (dev/prod/test).
	 *
	 * @var string
	 */
	private $environment;
	
	/**
	 * The kernel.
	 *
	 * @var Skelpo\Framework\Kernel\Kernel
	 */
	private $kernel;

	/**
	 * Returns the root dir of the application.
	 *
	 * @return string
	 */
	public function getRootDir()
	{
		return $this->kernel->getRootDir();
	}

	/**
	 * Returns the module.
	 *
	 * @return Skelpo\Framework\Module
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Sets the module.
	 *
	 * @param Skelpo\Framework\Module $m
	 */
	public function setModule(Module $m)
	{
		$this->module = $m;
	}

	/**
	 * Returns the root URL for this application.
	 * Consider though that
	 * this is not necessarily the only URL that is valid. Depending on
	 * your domain settings other domains count as well. But this one works
	 * 100% for sure.
	 *
	 * @return string The root URL that is used for this application.
	 */
	public function getRootUrl()
	{
		return $this->kernel->getRootUrl();
	}

	/**
	 * Returns the kernel.
	 *
	 * @return Skelpo\Framework\Kernel
	 */
	public function getKernel()
	{
		return $this->kernel;
	}

	/**
	 * Returns the dir for additional smarty plugins.
	 *
	 * @return string
	 */
	public function getSmartyPluginDir()
	{
		return $this->getRootDir() . 'engine/Skelpo/Framework/View/Plugins/';
	}

	/**
	 * Returns the dir for additional plugins.
	 *
	 * @return string
	 */
	public function getPluginDir()
	{
		return $this->getRootDir() . 'App/Plugins/';
	}

	/**
	 * Returns the current theme.
	 *
	 * @return Skelpo\Framework\Theme\Theme
	 */
	public function getTheme()
	{
		return $this->kernel->getTheme();
	}

	/**
	 * Returns the theme dir.
	 *
	 * @return string
	 */
	public function getThemeDir()
	{
		return $this->kernel->getThemeDir();
	}

	/**
	 * Returns all dirs that could contain templates.
	 *
	 * @return string[]
	 */
	public function getTemplateDirs()
	{
		$dirs = $this->getTheme()->getPaths();
		
		// get the template paths from the plugins
		$pluginPaths = $this->kernel->getContainer()->get('pluginmanager')->getPluginPaths();
		$themeHiearchy = $this->getTheme()->getThemeHierachy();
		foreach ($pluginPaths as $p)
		{
			foreach ($themeHiearchy as $themeName)
			{
				$d = $this->getKernel()->getRootDir() . $p . "Themes/" . $themeName . "/";
				if (is_dir($d))
				{
					$dirs[] = $d;
				}
				else
				{
					$dirs2[] = $d;
				}
			}
		}
		return $dirs;
	}

	/**
	 * Returns the cache dir.
	 *
	 * @return string
	 */
	public function getCacheDir()
	{
		return $this->kernel->getCacheDir();
	}

	/**
	 * Creates a new instance with the kernel as an argument.
	 *
	 * @param \Skelpo\Framework\Kernel\Kernel
	 */
	public function __construct(\Skelpo\Framework\Kernel\Kernel $kernel)
	{
		$this->kernel = $kernel;
		$this->eventDispatcher = new EventDispatcher();
	}

	public function build(ContainerBuilder $container)
	{
		parent::build($container);
		
		$container->addCompilerPass(new RoutingResolverPass());
		$container->addCompilerPass(new ProfilerPass());
		// must be registered before removing private services as some might be listeners/subscribers
		// but as late as possible to get resolved parameters
		$container->addCompilerPass(new RegisterListenersPass(), PassConfig::TYPE_BEFORE_REMOVING);
		$container->addCompilerPass(new AddConstraintValidatorsPass());
		$container->addCompilerPass(new AddValidatorInitializersPass());
		$container->addCompilerPass(new AddConsoleCommandPass());
		$container->addCompilerPass(new TranslatorPass());
		$container->addCompilerPass(new LoggingTranslatorPass());
		$container->addCompilerPass(new AddCacheWarmerPass());
		$container->addCompilerPass(new AddCacheClearerPass());
		$container->addCompilerPass(new TranslationExtractorPass());
		$container->addCompilerPass(new TranslationDumperPass());
		$container->addCompilerPass(new FragmentRendererPass(), PassConfig::TYPE_AFTER_REMOVING);
		$container->addCompilerPass(new SerializerPass());
		$container->addCompilerPass(new PropertyInfoPass());
		
		if ($container->getParameter('kernel.debug'))
		{
			$container->addCompilerPass(new UnusedTagsPass(), PassConfig::TYPE_AFTER_REMOVING);
			$container->addCompilerPass(new ContainerBuilderDebugDumpPass(), PassConfig::TYPE_AFTER_REMOVING);
			$container->addCompilerPass(new CompilerDebugDumpPass(), PassConfig::TYPE_AFTER_REMOVING);
			$container->addCompilerPass(new ConfigCachePass());
		}
	}

	/**
	 * Returns information.
	 *
	 * @return string[]
	 */
	public function getInfo()
	{
		return array(
				'author' => "Skelpo Inc." 
		);
	}

	/**
	 * Returns the event dispatcher.
	 *
	 * @return Symfony\Component\EventDispatcher\EventDispatcher
	 */
	public function getEventDispatcher()
	{
		return $this->eventDispatcher;
	}
}
