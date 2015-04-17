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
namespace Skelpo\Framework\Model\Models;

use Skelpo\Framework\Model\Model;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * This class is the model for a theme. 
 *
 * @ORM\Entity
 * @ORM\Table(name="themes")
 */
class Theme extends Model
{
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	 
	/**
	 * @ORM\Column(type="string", length=255, unique=true, nullable=false)
	 * @Assert\NotBlank
	 * @Assert\Length(min=3)
	 */
	protected $slug;
	
	/**
	 * @ORM\Column(type="string", length=255, nullable=false)
	 * @Assert\NotBlank
	 * @Assert\Length(min=3)
	 */
	protected $title;
	
	/**
	 * @ORM\Column(type="string", length=255, nullable=false)
	 * @Assert\NotBlank
	 * @Assert\Length(min=3)
	 */
	protected $name;
	 
	/**
	 * @ORM\Column(type="string", length=255, nullable=false)
	 * 
	 */
	protected $author;
	/**
	 * @ORM\Column(type="string", length=10, nullable=false)
	 * 
	 */
	protected $version;
	 /**
	 * @ORM\Column(type="smallint", length=1, nullable=false)
	 * 
	 */
	protected $active;
	 
	
	public function __construct()
	{
	    
	}
	
	
	    
}
