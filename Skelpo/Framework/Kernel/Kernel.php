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
/**
 * Kernel class.
 */
abstract class Kernel extends \Symfony\Component\HttpKernel\Kernel
{
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
	
	
}
