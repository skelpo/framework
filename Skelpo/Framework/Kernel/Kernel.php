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

namespace Skelpo\Framework\Kernel;

use Symfony\Component\HttpFoundation\Request;
use Skelpo\Framework\Framework;
use Skelpo\Framework\Plugin\PluginManager;
use Symfony\Component\Config\Loader\LoaderInterface;
use Skelpo\Framework\Kernel\KernelInterface;
/**
 * Kernel class.
 */
abstract class Kernel extends \Symfony\Component\HttpKernel\Kernel
{
	protected $theme;
	protected $framework;
	
	public function __construct($environment, $debug)
    {
    	parent::__construct($environment, $debug);
        
		$this->framework = new Framework($this);
		
		
    }
	
	public function boot()
	{
		parent::boot();
		$this->getContainer()->get("pluginmanager")->loadPlugins();
		$this->selectTheme();
        
	}
	
	protected function getListOfThemes()
	{
		$file = $this->getCacheDir()."themes.php";
		if (file_exists($file))
		{
			include($file);
		}
		else {
			$dbThemes = $this->getContainer()->get("doctrine")->getEntityManager()->getRepository('Skelpo\Framework\Model\Models\Theme')->findAll();
			$themes = array();
			foreach ($dbThemes as $p)
			{
				if ($p->getActive()==1)
				{
					$themes[] = array(
						"name" => $p->getName(),
						"slug" => $p->getSlug(),
						"active" => $p->getActive(),
						"folder" => $this->getThemeDir().$p->getName()
					);
				}
			}
			$phpcode = '<?php $themes = unserialize("'.addslashes(serialize($themes)).'"); ?>';
			$f = fopen($file,"w");
			fwrite($f,$phpcode);
			fclose($f);
		}
		return $themes;
	}
	
	protected function selectTheme()
	{
		$themes = $this->getListOfThemes();
		$theme = $themes[0];
		$n = "\\Themes\\".$theme['name']."\\".$theme['name'];
		// out of lazyness we just skip doing it properly right now
		$this->theme = new $n($this);
	}
	
	/**
     * {@inheritdoc}
     *
     * @api
     */
    public function handle(Request $request, $type = \Symfony\Component\HttpKernel\HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        if (false === $this->booted) {
            $this->boot();
        }
		
		
		 return $this->getHttpKernel()->handle($request, $type, $catch);
    }
	
	public function getThemeDir()
	{
		return $this->getRootDir()."Themes/";
	}
	public function getTheme()
	{
		return $this->theme;
	}
	public function getFramework()
	{
		return $this->framework;
	}
	/**
     * {@inheritdoc}
     *
     * @api
     */
    public function getRootDir()
    {
        if (null === $this->rootDir) {
            $r = new \ReflectionObject($this);
            $this->rootDir = str_replace('\\', '/', dirname($r->getFileName()))."/../";
        }
		return $this->rootDir;
    }
	
	public function getCacheDir()
    {
    	return $this->getRootDir().'cache/'.$this->getEnvironment().'/';
    }
	public function getConfigDir()
    {
        return $this->getRootDir().'config/';
    }
	public function getLogDir()
    {
        return $this->getRootDir().'logs/'.$this->getEnvironment().'/';
    }
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'config/config_'.$this->getEnvironment().'.yml');
    }
	public function getRootUrl()
	{
		return "/";
	}
	
	
}
