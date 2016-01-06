<?php

/**
 * This file is part of the skelpo framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @version 1.0.0
 * @author Ralph Kuepper <ralph.kuepper@skelpo.com>
 * @copyright 2016 Skelpo Inc. www.skelpo.com
 */
namespace Skelpo\Framework\Language;

use Skelpo\Framework\View\View;

/**
 * Langauge class that loads all necessary files and assemblies messages.
 *
 * Language messages can be defined (in any output) like this:
 * In outputs:
 * ##index.title##
 * ##user.message.body,"this is the text from a varible for the title","and this is the body text"##
 * In the language files:
 * $lang['index.title'] = "my title";
 * $lang['user.message.body'] = "my other message about $1 and contains: $2.";
 *
 * For plurals it works like this:
 * In outputs:
 * ##user.count:0## => "0 Users"
 * ##user.count:1## => "One user"
 * ##user.count:3## => "More, 3 Users";
 * In the language files:
 * $lang['user.count'] = "$0 Users";
 * $lang['user.count:1'] = "One user";
 * $lang['user.count:+1'] = "More, $0 User";
 * $lang['user.count:-10'] = "Less than $0 User";
 * $lagn['user.count:+4']
 */
class Language
{
	/**
	 *
	 * @var string The name of the language (de, de_DE, en_EN,...)
	 */
	protected $name;
	/**
	 *
	 * @var string[] All messages that are translations of keys.
	 */
	protected $messages;
	/**
	 *
	 * @var Skelpo\Framework\View\View Reference to the view object.
	 */
	protected $view;
	/**
	 *
	 * @var boolean Are we writing a file with all missing messages?
	 */
	protected $writeMissingFile;
	/**
	 *
	 * @var string File in which we write all missing messages.
	 */
	protected $missingFile;

	/**
	 * Creates a new language, initialed with a name and the view.
	 *
	 * @param View $v The view we are using.
	 * @param string $name The name of this language.
	 */
	public function __construct(View $v, $name)
	{
		$this->view = $v;
		$this->writeMissingFile = false;
		$this->missingFile = "App/Locale/missing." . $name . ".php";
		$this->name = $name;
		$this->messages = array();
	}

	/**
	 * Returns the name of this language.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Returns all messages saved for this language.
	 *
	 * @return string
	 */
	public function getMessages()
	{
		return $this->messages;
	}

	/**
	 * Loads one individual language file.
	 *
	 * @param string $path The path of the language file.
	 */
	public function loadLanguageFile($path)
	{
		$lang = array();
		include ($path);
		$this->addMessages($lang);
	}

	/**
	 * Loads all files from the given paths.
	 * Only the files matching the name of this language file will be loaded.
	 *
	 * @param string[] $paths The paths to all language file paths.
	 */
	public function loadLanguageFiles($paths)
	{
		foreach ($paths as $p)
		{
			if (is_dir($p))
			{
				$files = scandir($p);
				foreach ($files as $file)
				{
					
					if ($file == $this->name . ".php")
					{
						$this->loadLanguageFile($p . $file);
					}
				}
			}
		}
	}

	/**
	 * Adds a whole array of key to the existing messages.
	 * Keys will be overwritten.
	 *
	 * @param string[] $data Messages to add.
	 */
	public function addMessages($data)
	{
		$this->messages = array_merge($this->messages, $data);
	}

	/**
	 * Adds an individual message to the array.
	 *
	 * @param string $key The key for this message.
	 * @param string $value The value for this message.
	 */
	public function addMessage($key, $value)
	{
		$this->messages[$key] = $value;
	}

	/**
	 * Translates a string.
	 *
	 * @param string $termn The term to translate.
	 * @return string Translated term.
	 */
	public function getString($term)
	{
		if (is_array($term))
			$term = $term[1];
		$ret = preg_replace_callback("/##(.+?)##/", array(
				$this,
				"getString" 
		), $term);
		if (substr($term, - 2) != "##" && substr($term, 0, 2) != "##")
		{
			$k = $term;
			$count = 0;
			if (stristr($k, ":"))
			{
				$p1 = strpos($k, ":");
				$k = substr($term, 0, $p1);
				$count = substr($term, $p1 + 1);
			}
			$paras = array();
			$paras[] = $count;
			
			if (stristr($k, ","))
			{
				$p2 = strpos($k, ",");
				$mm = stripslashes(substr($k, $p2 + 1));
				$matches = "\$strings = array(" . $mm . ");";
				eval($matches);
				$k = substr($k, 0, $p2);
				$paras = array_merge($paras, $strings);
			}
			if (func_num_args() > 1)
			{
				$pp = func_get_args();
				unset($pp[0]);
				$paras = array_merge($paras, $pp);
			}
			if (! array_key_exists($k, $this->messages))
			{
				$paras = array();
				$paras[0] = $k;
				$k = "not.found.";
				if (! array_key_exists($k, $this->messages))
				{
					$k = "not.found." . $paras[0];
				}
				if ($this->writeMissingFile)
				{
					$this->addToMissingFile($k, $paras);
				}
			}
			if (array_key_exists($k, $this->messages))
			{
				$m = $this->messages[$k];
			}
			else
			{
				$m = $k;
			}
			foreach ($paras as $a => $p)
			{
				$m = str_replace('$' . $a, $p, $m);
			}
			return $m;
		}
		return "error: " . $term;
	}
}
