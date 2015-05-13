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

namespace Skelpo\Framework\Model\Models\Menu;

use Skelpo\Framework\Model\Model;

abstract class Entry extends Model
{
	protected $label;
	protected $subMenu;
	
	public function __construct($l)
	{
		$this->label = $l;
		$this->subMenu = null;
	}
	
	public function hasSubmenu()
	{
		return !is_null($this->subMenu);
	}
	
}