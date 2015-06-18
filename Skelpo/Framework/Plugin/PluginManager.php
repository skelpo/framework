<?php

/**
 * This file is part of the skelpo framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @version 1.0.0-alpha
 * @author Ralph Kuepper <ralph.kuepper@skelpo.com>
 * @copyright 2015 Skelpo Inc. www.skelpo.com
 */
namespace Skelpo\Framework\Plugin;

use Skelpo\Framework\Framework;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * The plugin manager installs/uninstalls and works with the plugins.
 */
class PluginManager
{
	protected $framework;
	protected $entityManager;
	protected $plugins;
	protected $modelNamespace;

	/**
	 * Create a new plugin manager.
	 *
	 * @param Framework $f
	 * @param EntityManager $em
	 */
	public function __construct(Framework $f, EntityManager $em)
	{
		$this->framework = $f;
		$this->entityManager = $em;
	}

	/**
	 * Add a plugin.
	 *
	 * @param Plugin $p
	 */
	public function addPlugin(Plugin $p)
	{
		$this->plugins[] = $p;
	}

	/**
	 * Load all plugins.
	 */
	public function loadPlugins($modelNamespace)
	{
		$this->modelNamespace = $modelNamespace;
		$c = $this->framework->getKernel()->getCache("plugins");
		$c->setLifetime(0);
		$plugins = $c->getContent();
		if (is_null($plugins) || $plugins == "")
		{
			$dbPlugins = $this->entityManager->getRepository($this->modelNamespace)->findAll();
			$plugins = array();
			foreach ($dbPlugins as $p)
			{
				if ($p->getActive() == 1)
				{
					$plugins[] = array(
							"name" => $p->getName(),
							"slug" => $p->getSlug(),
							"folder" => $this->framework->getPluginDir() . $p->getName() 
					);
				}
			}
			$c->setContent($plugins);
		}
		$this->plugins = array();
		foreach ($plugins as $plugin)
		{
			$pluginRefClass = new \ReflectionClass('App\\Plugins\\' . $plugin['name'] . '\\' . $plugin['name']);
			$plugin = $pluginRefClass->newInstance($this->framework, $this->framework->getKernel());
			$plugin->boot();
			$this->plugins[] = $plugin;
		}
	}

	protected function getPlugin($name)
	{
		foreach ($this->plugins as $plugin)
		{
			$pluginRefClass = new \ReflectionClass($plugin);
			if ($pluginRefClass->getShortName() == $name)
			{
				return $plugin;
			}
		}
		throw new \InvalidArgumentException("Plugin not found");
	}

	/**
	 * Installs a plugin
	 *
	 * @param integer $p
	 */
	public function installPlugin($pluginId)
	{
		$plugins = $this->entityManager->getRepository($this->modelNamespace)->findById($pluginId);
		if (count($plugins) != 1)
		{
			throw new PluginNotFoundException("The plugin cannot be found");
		}
		$plugin = $plugins[0];
		if ($plugin->isActive())
		{
			throw new PluginStatusException("Plugin is already installed.");
		}
		$pluginInstance = $this->getPlugin($plugin->getName());
		$plugin->setActivated(true);
		$app = new Application($this->framework->getKernel());
		$app->setAutoExit(false);
		$input = new StringInput("doctrine:schema:update --force");
		$fp = fopen('php://temp/maxmemory:' . (1024 * 1024 * 512), 'r+');
		$output = new StreamOutput($fp);
		$error = $app->run($input, $output);
		rewind($fp);
		if ($error != 0)
		{
			$msg = ("Error: $error\n" . stream_get_contents($fp));
			throw new PluginStatusException($msg);
		}
		else
			$msg = stream_get_contents($fp);
		
		$this->entityManager->flush();
		
		$pluginInstance->install();
	}

	/**
	 * Reinstalls a plugin.
	 *
	 * @param integer $p
	 */
	public function reinstallPlugin($pluginId)
	{
		$this->uninstallPlugin($pluginId);
		$this->installPlugin($pluginId);
	}

	/**
	 * Deinstalls a plugin.
	 *
	 * @param integer $p
	 */
	public function uninstallPlugin($pluginId)
	{
		$plugins = $this->entityManager->getRepository($this->modelNamespace)->findById($pluginId);
		if (count($plugins) != 1)
		{
			throw new PluginNotFoundException("The plugin cannot be found");
		}
		$plugin = $plugins[0];
		if (! $plugin->isActive())
		{
			throw new PluginStatusException("Plugin is already uninstalled.");
		}
		$pluginInstance = $this->getPlugin($plugin->getName());
		$pluginInstance->uninstall();
		$plugin->setActive(0);
		$this->entityManager->flush();
	}
}
