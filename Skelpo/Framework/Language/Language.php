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
 * $lang['user.message.body'] = "my other message about {1} and contains: {2}.";
 * 
 * For plurals it works like this:
 * In outputs:
 * ##user.count:0## => "0 Users"
 * ##user.count:1## => "One user"
 * ##user.count:3## => "More, 3 Users";
 * In the language files:
 * $lang['user.count'] = "{0} Users";
 * $lang['user.count:1'] = "One user";
 * $lang['user.count:+1'] = "More, {0} User";
 * $lang['user.count:-10'] = "Less than {0} User";
 * $lagn['user.count:+4']
 * 
 */
class Language
{
	protected $name;
	protected $messages;
	protected $view;
	
	protected $writeMissingFile;
	protected $missingFile;
	
	public function __construct(View $v, $name)
	{
		$this->view = $v;
		$this->writeMissingFile = false;
		$this->missingFile = "App/Locale/missing.".$name.".php";
		$this->name = $name;
	}
	
	public function getString($term)
	{
		$ret = preg_replace("/##(.+?)##/e","\$this->getString('\\1')",$ret);
        if (substr($term,-2)!="##" && substr($term,0,2)!="##")
		{
			$k = $term;
			$count = 0;
			if (stristr($k,":")) {
				$p1 = strpos($k,":");
				$k = substr($term,0,$p1);
				$count = substr($term,$p1+1);
			}
			$paras = array();
			$paras[] = $count;
			//if (stristr($k,","))
			//preg_match_all('/(?:[^\']|\\\\.)+|(?:[^"]|\\\\.)+/', $term, $matches);
			
			
			if (!array_key_exists($k, $this->messages))
			{
				$k = "not.found";
			}
			$m = $this->messages[$k];
			if ($m=="") {
				return "error: ".$term;
			}
			return $m;
		}
		return "error: ".$term;
	}
	
	
}
