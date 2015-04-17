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
		$file = $this->framework->getCacheDir()."plugins.php";
		if (file_exists($file))
		{
			include($file);
		}
		else {
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
			$phpcode = '<?php $plugins = unserialize("'.addslashes(serialize($plugins)).'"); ?>';
			$f = fopen($file,"w");
			fwrite($f,$phpcode);
			fclose($f);
		}
		
	}
	public function installPlugin(Plugin $p)
	{
		
	}
	public function deinstallPlugin(Plugin $p){
		
	}
	
}
