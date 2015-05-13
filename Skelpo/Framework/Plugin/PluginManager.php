<?php

namespace Skelpo\Framework\Plugin;

use Skelpo\Framework\Framework;
use Doctrine\ORM\EntityManager;

class PluginManager {
	protected $framework;
	protected $entityManager;
	protected $plugins;
	
	public function __construct(Framework $f, EntityManager $em)
	{
		$this->framework = $f;
		$this->entityManager = $em;
	}
	
	public function addPlugin(Plugin $p)
	{
		$this->plugins[] = $p;
	}
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
				if ($p->getActive()==1)
				{
					$plugins[] = array(
						"name" => $p->getName(),
						"slug" => $p->getSlug(),
						"folder" => $this->framework->getPluginDir().$p->getName()
					);
				}
			}
			$c->setContent($plugins);
		}
		
		
	}
	public function installPlugin(Plugin $p)
	{
		
	}
	public function deinstallPlugin(Plugin $p){
		
	}
	
}
