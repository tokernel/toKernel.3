<?php
/**
 * toKernel by David A. and contributors.
 * Main request class for CLI (Command line interface) mode.
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
 * @subpackage kernel
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
 * Request class
 *
 * @author David A. <tokernel@gmail.com>
 */
class Request {
		
	/**
	 * Status of this class initialization
	 *
	 * @access private
	 * @staticvar bool
	 */
	private static $initialized = false;
		
	/**
	 * CLI Request Configuration
	 *
	 * @access private
	 * @var array
	 */
	private static $request;
	
	/**
	 * Private constructor to prevent it being created directly
	 *
	 * @final
	 * @access private
	 */
	final private function __construct() {
		
	}
	
	/**
	 * Prevent cloning of the object.
	 *
	 * @throws ErrorException
	 * @access public
	 * @return void
	 */
	public function __clone() {
		throw new ErrorException('Cloning the object is not permitted ('.__CLASS__.')', E_USER_ERROR );
	}
		
	/**
	 * Initialization of request calls at once from framework loader.
	 *
	 * @access public
	 * @throws ErrorException
	 * @param array $args
	 * @param object $config
	 * @return bool
	 */
	public static function init(array $args, $config) {
		
		// Request can be initialized only once.
		if(self::$initialized == true) {
			throw new ErrorException('Request initialization - ' . __CLASS__ . '->' . __FUNCTION__ . '() is already initialized!');
		}

		// Initialize Client library
        \Lib\Client::init();

		// For the first, cleanup all arguments if set in config 1
		if($config->getItem('cli_auto_clean_args', 'CLI') == 1) {
			$args = \Lib\Filter::cleanData($args);
		}
				
		// Check arguments count
		if(count($args) < 2) {
			
			Response::outputUsage("Invalid Command line arguments!");
						
			exit(1);
		}
		
		// Show usage on screen and exit, if called help action.
		if(in_array($args[1], array('--help', '--usage', '-help', '-h', '-?'))) {

			Response::outputUsage();
			exit(0);
		}

		// Parse Routing and set configuration
		self::$request = Routing::parseCliArgs($args);

		// Clean Addon and Action names if requested with "--" symbols.
		self::$request['addon'] = self::cleanArg(self::$request['addon']);
		self::$request['action'] = self::cleanArg(self::$request['action']);
		
		// Check if action not empty.
		if(self::$request['action'] == '') {
			Response::outputUsage("Action is empty.");
			exit(1);
		}
								
		self::$initialized = true;
		
		return true;
		
	} // End func init
	
	/**
	 * Read data from command line in interactive mode.
	 *
	 * @access public
	 * @return string
	 */
	public static function in() {
		$handle = trim(fgets(STDIN));
		return $handle;
	} // end func in
	
	/**
	 * Return language prefix
	 *
	 * @access public
	 * @return string
	 */
	public static function languagePrefix() {
		return self::$request['language_prefix'];
	}
	
	/**
	 * Return addon id
	 *
	 * @access public
	 * @return string
	 */
	public static function addon() {
		return self::$request['addon'];
	}
	
	/**
	 * Return action of addon
	 *
	 * @access public
	 * @return string
	 */
	public static function action() {
		return self::$request['action'];
	}
		
	/**
	 * Return parameter value by name or parameters array
	 *
	 * @access public
	 * @param string $item
	 * @return mixed array | string | bool
	 */
	public static function cliArgs($item = NULL) {
		
		// Return CLI arguments array
		if(is_null($item)) {
			return self::$request['cli_args'];
		}
		
		// Return argument value by index
		if(isset(self::$request['cli_args'][$item])) {
			return self::$request['cli_args'][$item];
		}
		
		// Argument not exists
		return false;
		
	} // end func cliArgs
	
	/**
	 * Return CLI arguments count
	 *
	 * @access public
	 * @return integer
	 */
	public static function cliArgsCount() {
		return count(aelf::$request['cli_args']);
	}
	
	/**
	 * Clean argument
	 * Remove first "--" chars if exists.
	 *
	 * @access protected
	 * @param string $arg
	 * @return string
	 */
	protected static function cleanArg($arg) {
		
		if (substr($arg, 0, 2) == '--') {
			$arg = substr($arg, 2);
		}
		
		return $arg;
		
	} // End func cleanArg
		
} // End class Request