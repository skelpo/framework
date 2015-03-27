<?php

namespace Skelpo\Framework\View;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Skelpo\Framework\Events\ControllerEvent;
use Skelpo\Framework\Framework;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Filesystem\Filesystem;

class View extends \Smarty
{
	private $framework;
	private $templateFile;
	private $module;
	private $filesystem;
	private $minifyJs;
	private $minifyCss;
	
	public function __construct(Framework $f)
	{
		parent::__construct();
			
		$this->framework = $f;
		$this->templateFile = "";
		$this->filesystem = new Filesystem();
		$this->minifyJs = true;
		$this->minifyCss = true;
		
		$this->setupSmarty();
	}
	
	private function setupSmarty()
	{
		$p = $this->framework->getCacheDir()."smarty/";
		$this->setCacheDir($p);
		$this->setCompileDir($p."compile/");
		$this->setTemplateDir($this->framework->getTemplateDirs());
		$this->error_reporting = 0;
		$this->caching = \Smarty::CACHING_LIFETIME_CURRENT;
	}
	
	private function setupTemplateDirs()
	{
		$startDirs = array();
		//$startDirs = $this->framework->getTemplateDirs();
	}
	public function isCacheDue()
	{
		return true; // TODO: Fix this.
	}
	public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
		
        /*
         * $controller passed can be either a class or a Closure.
         * This is not usual in Symfony but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller)) {
            return;
        }
		$controller = $controller[0];
		$request = $event->getRequest();
		$s = $request->attributes->get('_controller');
		if (!stristr($s,"::")) return;
		//die("A:".$request->attributes->get('_controller'));
		$params = explode('::',$s);
		$actionName = substr($params[1],0,-6);
		$controllerName = substr($params[0],0,-10);
		$eventName = str_replace("\\","_",$controllerName)."_".ucwords($actionName)."_PostDispatch";
		$dispatcher = $this->framework->getEventDispatcher();
		$cevent = new ControllerEvent($controller);
		$dispatcher->dispatch($eventName, $cevent);
		
		
		$module = substr($controllerName,strpos($controllerName,"Controllers")+12);
		$this->module = strtolower(substr($module,0,strpos($module,"\\")));
		
		if (in_array($this->module, array("api", "widgets")))
		{
			return;
		}
		
		
		
		$templateName = strtolower(str_replace("\\", "/",substr($controllerName,strpos($controllerName,"Controllers")+12)))."/".$actionName.".tpl";
		$defaultTemplateName = strtolower(str_replace("\\", "/",substr($controllerName,strpos($controllerName,"Controllers")+12)))."/index.tpl";
		
		$dirs = $this->framework->getTemplateDirs();
		foreach ($dirs as $dir)
		{
			if ($this->filesystem->exists($dir.$templateName))
			{
				$this->templateFile = $templateName;
				break;
			}
		}
		if ($this->templateFile=="")
		{
			foreach ($dirs as $dir)
			{
				if ($this->filesystem->exists($dir.$defaultTemplateName))
				{
					$this->templateFile = $defaultTemplateName;
					break;
				}
			}
		}
		
		if ($this->templateFile=="")
		{
			throw new Symfony\Component\HttpKernel\Exception\BadRequestHttpException("Template does not exist.");
		}
		
		
    }
	private function getLessUrl()
	{
		$cssfile = $this->framework->getRootDir()."static/css/all.css";
		$cssurl = $this->framework->getRootUrl()."static/css/all.css";
		
		if ($this->filesystem->exists($cssfile) && !$this->isCacheDue()) 
		{
			return $cssurl;
		}
		$dirs = $this->framework->getTemplateDirs();
		$allLess = "";
		foreach ($dirs as $dir_)
		{
			$file = $dir_ . $this->module."/_public/less/all.less";
			if ($this->filesystem->exists($file))
			{
				$allLess = $file;
				break;
			}
		}
		if ($allLess=="") return;
		try{
			$parser = new \Less_Parser();
			\Less_Parser::$options['compress'] = $this->minifyCss;
			$parser->parseFile( $allLess, '/static/' );
			$css = $parser->getCss();
		}catch(Exception $e){
			$error_message = $e->getMessage();
		}
		if ($this->filesystem->exists($cssfile)) $this->filesystem->remove($cssfile);
		$this->filesystem->dumpFile($cssfile, $css);
		return $cssurl;
	}
	
	private function getJSUrl()
	{
		$jsfile = $this->framework->getRootDir()."static/js/all.js";
		$jsurl = $this->framework->getRootUrl()."static/js/all.js";
		if ($this->filesystem->exists($jsfile) && !$this->isCacheDue()) 
		{
			return $jsurl;
		}
		$files = $this->framework->getTheme()->getJSFiles();
		$dirs = $this->framework->getTemplateDirs();
		$dir_ = $dirs[0];
		$allLess = "";
		$jsoutput = "";
		foreach ($files as $file_)
		{
			$file = $dir_ . $this->module."/_public/js/".$file_;
			if ($this->filesystem->exists($file))
			{
				$jsoutput.=file_get_contents($file)."\n";
				
			}
		}
		
		if ($this->minifyJs) $jsoutput = \JShrink\Minifier::minify($jsoutput, array('flaggedComments' => false));
		if ($this->filesystem->exists($jsfile)) $this->filesystem->remove($jsfile);
		$this->filesystem->dumpFile($jsfile, $jsoutput);
		return $jsurl;
	}
	
	public function onKernelResponse(FilterResponseEvent $event)
	{
		if (in_array($this->module, array("api", "widgets")))
		{
			return;
		}
		$cssUrl = $this->getLessUrl();
		$jsUrl = $this->getJSUrl();
		
		$this->assign("cssUrl", $cssUrl);
		$this->assign("jsUrl", $jsUrl);
		
		$content = $this->fetch($this->templateFile);
		
		$res = false;
		try {
			$res = $event->getResponse();
		}
		catch (Exception $e)
		{
			//die("F:".print_r($e,true));
		}
		$newResponse = new Response();
		$newResponse->setContent($content);
		$newResponse->setStatusCode(200);
	    $event->setResponse($newResponse);
	}

}
