<?php
/**
 * toKernel by David A. and contributors.
 * Base Routing class
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
 * @subpackage base
 * @author     David A. <framework@tokernel.com>
 * @copyright  Copyright (c) 2017 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 3.0.0
 */

namespace Kernel\Base;

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * Routing class
 *
 * @author David A. <tokernel@gmail.com>
 */
class Routing {
				
	/**
	 * Cleanup array arguments
	 *
	 * @static
	 * @access protected
	 * @param array $queryArr
	 * @return array
	 */
	protected static function clean($queryArr) {

		foreach($queryArr as $index => $value) {

			if(trim($value) == '') {
				unset($queryArr[$index]);
			}

		}

		return $queryArr;

	} // End func clean

	/**
	 * Compare route to detect matching
	 *
	 * @static
	 * @access protected
	 * @param array $queryArr
	 * @param array $routeDefArr
	 * @param array $routeValArr
	 * @return mixed array|boolean
	 */
	protected static function compareRoute($queryArr, $routeDefArr, $routeValArr) {

		if(count($queryArr) != count($routeDefArr)) {
			return false;
		}

		$vars = array();

		for($i = 0; $i < count($queryArr); $i++) {

			$var = self::isVar($routeDefArr[$i]);

			if($var !== false) {

				$add = false;

				// Check if var is valid by required
				if($routeDefArr[$i] == '{var.id}') {

				    if(\Lib\Valid::id($queryArr[$i])) {
						$add = true;
					}

				} elseif($routeDefArr[$i] == '{var.num}') {

				    if(is_numeric($queryArr[$i])) {
						$add = true;
					}

				} elseif($routeDefArr[$i] == '{var.any}') {
					$add = true;
				}

				if($add == true) {
					$vars[] = $queryArr[$i];
					$queryArr[$i] = $routeDefArr[$i];
				}

			}

		}

		if(implode('/', $queryArr) == implode('/', $routeDefArr)) {

			$nqs = array();
			$varsSet = 0;

			foreach($routeValArr as $val) {

				if(substr($val, 0, 5) == '{var}' and isset($vars[$varsSet])) {
					$nqs[] = $vars[$varsSet];
					$varsSet++;
				} else {
					$nqs[] = $val;
				}

			}

			return $nqs;
		}

		return false;

	} // End func compareRoute

	/**
	 * Check if the given value is route defined variable
	 *
	 * @static
	 * @access private
	 * @param string $str
	 * @return bool
	 */
	private static function isVar($str) {

		$vars = array(
			'{var.id}' => 'Integer',
			'{var.num}' => 'Number',
			'{var.any}' => 'Any value'
		);

		if(isset($vars[$str])) {
			return $vars[$str];
		}

		return false;

	} // End func isVar
	
} // End class Routing