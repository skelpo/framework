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

/**
 * The plugin manager installs/uninstalls and works with the plugins.
 */
class PluginManager
{
	protected $framework;
	protected $entityManager;
	protected $plugins;

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
	public function loadPlugins()
	{
		$c = $this->framework->getKernel()->getCache("plugins");
		$c->setLifetime(0);
		$plugins = $c->getContent();
		if (is_null($plugins))
		{
			$dbPlugins = $this->entityManager->getRepository('Skelpo\Framework\Model\Models\Plugin')->findAll();
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
	}

	/**
	 * Installs a plugin
	 * 
	 * @param Plugin $p
	 */
	public function installPlugin(Plugin $p)
	{
		// TODO: complete this section
	}

	/**
	 * Deinstalls a plugin.
	 * 
	 * @param Plugin $p
	 */
	public function deinstallPlugin(Plugin $p)
	{
		// TODO: complete this section
	}
}
