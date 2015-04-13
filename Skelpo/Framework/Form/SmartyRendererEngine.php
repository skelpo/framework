<?php

namespace Skelpo\Framework\Form;

use Symfony\Component\Form\AbstractRendererEngine;
use Symfony\Component\Form\FormView;
use Skelpo\Framework\View\Template;
use Skelpo\Framework\Framework;

class SmartyRendererEngine extends AbstractRendererEngine implements SmartyRendererEngineInterface
{
    

    /**
     * @var Framework instance
     */
    protected $framework;
	
	protected $router;

    /**
     * {@inheritdoc}
     */
    public function setFramework(Framework $s)
    {
        $this->framework = $s;
    }
	public function setRouter( $router)
	{
		$this->router = $router;
	}
	public function getRouter()
	{
		return $this->router;
	}
	protected function getTemplate($typeName)
	{
		if ($typeName == "text")
		{
			return '<input type="text" name="{$name}" value="{$value}" placeholder="{$placeholder}" id="{$id}" class="{$class}" />';
		}
		if ($typeName == "password")
		{
			return '<input type="password" name="{$name}" value="{$value}" placeholder="{$placeholder}" id="{$id}" class="{$class}" />';
		}
		if ($typeName == "submit")
		{
			return '<input class="{$class}" type="submit" value="{$value}" />';
		}
		
	}
	/**
	 * 
	 */
	public function renderInput(\Symfony\Component\Form\FormInterface $view, $blockName, $params)
	{
		$pp = explode("_", strtolower($blockName));
		$cfg = $view->getConfig();
		$type = $cfg->getType();
		$templateName = $pp[1]."/".$pp[0]."/".$pp[2]."/".$pp[3]."/".$type->getName().".tpl";
		$templateName2 = $pp[1]."/".$pp[0]."/".$pp[2]."/".$pp[3]."/".$type->getName()."-".$view->getName().".tpl";
		$template = new Template($this->framework);
		if ($template->templateExists($templateName)) $template->setTemplateFile($templateName);
		else if ($template->templateExists($templateName2)) $template->setTemplateFile($templateName2);
		if ($view->getName()=="submit")
		{
			//return print_r($cfg->getOptions(),true);
			$template->assign("value", $cfg->getOption('label'));
		
		}
		$template->assign("id", $params['id']);
		$template->assign("placeholder", $cfg->getOption('label')); //$view->vars['label']);
		$template->assign("class", $params['class']." ".$view->getName());
		$template->assign("name", $view->getName());
		if ($template->exists())
		{
			
			$c = $template->getContent();
			
		}
		else {
			$c = $template->fetch("string:".$this->getTemplate($type->getName()));
		}
		return $c;
		return "no";
	}
	/**
	 * 
	 */
	public function renderForm(FormView $view, $blockName, $content, $params)
	{
		//return get_class($this->resources[$cacheKey][$blockName]);
    	$pp = explode("_", strtolower($blockName));
		$templateName = $pp[1]."/".$pp[0]."/".$pp[2]."/".$pp[3].".tpl";
		$template = new Template($this->framework, $templateName);
		if ($template->exists())
		{
			$contentTemplate = new Template($this->framework, null);
			$template->assign("content", $content);
			
			$action = $view->vars['action'];
			
			$action = $this->router->generate($action);
			
			$template->assign("action", $view->vars['action']);
			$template->assign("id", $params['id']);
			$template->assign("class", $params['class']);
			$template->assign("name", $params['name']);
			if ($params['method']=="") $params['method'] = "post";
			$template->assign("method", $params['method']);
			
			$c = $template->getContent();
			return $c;
		}
		return "no";
	}
    /**
     * {@inheritdoc}
     */
    public function renderBlock(FormView $view, $resource, $blockName, array $variables = array())
    {
    	//return get_class($this->resources[$cacheKey][$blockName]);
    	$pp = explode("_", $blockName);
		return print_r($pp,true);
    	$templateName = "";
		$ret = "";
		foreach ($view as $a=> $b)
		{
			$ret .= print_r(($b->vars['value']),true)."<br />";
			
			//$ret .= "(".count($b->vars['value']).")";
			//if (isset($b->vars['value'])) $ret .= (" F:".($b->vars[0]->getName()));
		}
		return $ret;
    	$cacheKey = $view->vars[self::CACHE_KEY_VAR];

        $context = $this->environment->mergeGlobals($variables);

        ob_start();

        // By contract,This method can only be called after getting the resource
        // (which is passed to the method). Getting a resource for the first time
        // (with an empty cache) is guaranteed to invoke loadResourcesFromTheme(),
        // where the property $template is initialized.

        // We do not call renderBlock here to avoid too many nested level calls
        // (XDebug limits the level to 100 by default)
        $this->template->displayBlock($blockName, $context, $this->resources[$cacheKey]);

        return ob_get_clean();
    }

    /**
     * Loads the cache with the resource for a given block name.
     *
     * This implementation eagerly loads all blocks of the themes assigned to the given view
     * and all of its ancestors views. This is necessary, because Twig receives the
     * list of blocks later. At that point, all blocks must already be loaded, for the
     * case that the function "block()" is used in the Twig template.
     *
     * @see getResourceForBlock()
     *
     * @param string   $cacheKey  The cache key of the form view.
     * @param FormView $view      The form view for finding the applying themes.
     * @param string   $blockName The name of the block to load.
     *
     * @return bool True if the resource could be loaded, false otherwise.
     */
    protected function loadResourceForBlockName($cacheKey, FormView $view, $blockName)
    {
    	$this->resources[$cacheKey][$blockName] = new Template($this->framework, $blockName); //$view->getName();
    	return true;
    	// The caller guarantees that $this->resources[$cacheKey][$block] is
        // not set, but it doesn't have to check whether $this->resources[$cacheKey]
        // is set. If $this->resources[$cacheKey] is set, all themes for this
        // $cacheKey are already loaded (due to the eager population, see doc comment).
        if (isset($this->resources[$cacheKey])) {
            // As said in the previous, the caller guarantees that
            // $this->resources[$cacheKey][$block] is not set. Since the themes are
            // already loaded, it can only be a non-existing block.
            $this->resources[$cacheKey][$blockName] = false;

            return false;
        }

        // Recursively try to find the block in the themes assigned to $view,
        // then of its parent view, then of the parent view of the parent and so on.
        // When the root view is reached in this recursion, also the default
        // themes are taken into account.

        // Check each theme whether it contains the searched block
        if (isset($this->themes[$cacheKey])) {
            for ($i = count($this->themes[$cacheKey]) - 1; $i >= 0; --$i) {
                $this->loadResourcesFromTheme($cacheKey, $this->themes[$cacheKey][$i]);
                // CONTINUE LOADING (see doc comment)
            }
        }

        // Check the default themes once we reach the root view without success
        if (!$view->parent) {
            for ($i = count($this->defaultThemes) - 1; $i >= 0; --$i) {
                $this->loadResourcesFromTheme($cacheKey, $this->defaultThemes[$i]);
                // CONTINUE LOADING (see doc comment)
            }
        }

        // Proceed with the themes of the parent view
        if ($view->parent) {
            $parentCacheKey = $view->parent->vars[self::CACHE_KEY_VAR];

            if (!isset($this->resources[$parentCacheKey])) {
                $this->loadResourceForBlockName($parentCacheKey, $view->parent, $blockName);
            }

            // EAGER CACHE POPULATION (see doc comment)
            foreach ($this->resources[$parentCacheKey] as $nestedBlockName => $resource) {
                if (!isset($this->resources[$cacheKey][$nestedBlockName])) {
                    $this->resources[$cacheKey][$nestedBlockName] = $resource;
                }
            }
        }

        // Even though we loaded the themes, it can happen that none of them
        // contains the searched block
        if (!isset($this->resources[$cacheKey][$blockName])) {
            // Cache that we didn't find anything to speed up further accesses
            $this->resources[$cacheKey][$blockName] = false;
        }

        return false !== $this->resources[$cacheKey][$blockName];
    }

    /**
     * Loads the resources for all blocks in a theme.
     *
     * @param string $cacheKey The cache key for storing the resource.
     * @param mixed  $theme    The theme to load the block from. This parameter
     *                         is passed by reference, because it might be necessary
     *                         to initialize the theme first. Any changes made to
     *                         this variable will be kept and be available upon
     *                         further calls to this method using the same theme.
     */
    protected function loadResourcesFromTheme($cacheKey, &$theme)
    {
        if (!$theme instanceof \Template) {
            /* @var \Twig_Template $theme */
            $theme = $this->smarty->loadTemplate($theme);
        }

        if (null === $this->template) {
            // Store the first \Twig_Template instance that we find so that
            // we can call displayBlock() later on. It doesn't matter *which*
            // template we use for that, since we pass the used blocks manually
            // anyway.
            $this->template = $theme;
        }

        // Use a separate variable for the inheritance traversal, because
        // theme is a reference and we don't want to change it.
        $currentTheme = $theme;

        //$context = $this->environment->mergeGlobals(array());
		return;
        // The do loop takes care of template inheritance.
        // Add blocks from all templates in the inheritance tree, but avoid
        // overriding blocks already set.
        do {
            foreach ($currentTheme->getBlocks() as $block => $blockData) {
                if (!isset($this->resources[$cacheKey][$block])) {
                    // The resource given back is the key to the bucket that
                    // contains this block.
                    $this->resources[$cacheKey][$block] = $blockData;
                }
            }
        } while (false !== $currentTheme = $currentTheme->getParent($context));
    }
}