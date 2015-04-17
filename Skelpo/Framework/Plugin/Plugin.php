<?php

namespace Skelpo\Framework\Plugin;

abstract class Plugin {
	protected $framework;
	protected $kernel;
	
	public function __construct($framework, $kernel)
	{
		
	}
	
	protected abstract function init();
	
	protected abstract function install() 
	{
		
	}
	
	protected abstract function uninstall()
	{
		
	}
	
	protected  function update(){
		
	}
	
	
	public function subscribeEvent($eventName, $function)
	{
		
	}
}
