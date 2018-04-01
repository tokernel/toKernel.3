<?php
/**
 * toKernel by David A. and contributors.
 * CLI Routing class
 *
 * This file is part of toKernel.
 *
 * toKernel is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * toKernel is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with toKernel. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category   kernel
 * @package    framework
 * @subpackage CLI
 * @author     David A. <framework@tokernel.com>
 * @copyright  Copyright (c) 2017 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 3.0.0
 */

namespace CLI;

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * Routing class
 *
 * @author David A. <tokernel@gmail.com>
 */
class Routing extends \Kernel\Base\Routing {

    /**
     * Parse CLI (Command Line Interface) Arguments
     *
     * @access public
     * @param array $args
     * @return array
     */
	public static function parseCliArgs($args) {
		
		$data = array(
			'cli_args' => $args,
			'language_prefix' => 'en',
			'addon' => '',
			'action' => '',
			'route_parsed' => '',
			'cli_args_orig' => $args
		);
		
		$data = array_merge($data, self::parseRoute($data));
		
		$data['addon'] = array_shift($data['cli_args']);
		
		if(!empty($data['cli_args'])) {
			$data['action'] = array_shift($data['cli_args']);
		}
		
		$data['addon'] = strtolower($data['addon']);
		$data['action'] = strtolower($data['action']);

		return $data;

	} // End func parseCliArgs
	
	/**
	 * Parse routing
	 *
	 * @static
	 * @access private
	 * @param array $data
	 * @return array
	 */
    private static function parseRoute($data) {

		$data['cli_args_orig'] = $data['cli_args'];
		
		/*
		* Remove first element of $args array,
		* that will be the file name: index.php
		*/
		array_shift($data['cli_args']);
				
		$queryArr = $data['cli_args'];
		
		$routesIni = new \Lib\Ini(TK_APP_PATH . 'Config' . TK_DS . 'routes.ini');
		
		// Cleanup array values
		$queryArr = self::clean($queryArr);
		
		$routes = $routesIni->getSection('CLI');

		// Parse each route to detect matching
		foreach($routes as $item => $value) {
			
			$r_arr = explode('/', trim($item, '/'));
			$v_arr = explode('/', trim($value, '/'));
			
			$nqs = self::compareRoute($queryArr, $r_arr, $v_arr);
			
			if($nqs !== false) {
								
				$data['route_parsed'] = $item.'='.$value;
				$data['cli_args'] = $nqs;
				
				return $data;
			}
			
		}
						
		unset($routesIni);
		
		return $data;
		
	} // End func parseRoute
	
} // End of class Routing
