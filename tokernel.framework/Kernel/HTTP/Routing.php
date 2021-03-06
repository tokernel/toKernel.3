<?php
/**
 * toKernel by David A. and contributors.
 * HTTP Routing class
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
 * @subpackage HTTP
 * @author     David A. <framework@tokernel.com>
 * @copyright  Copyright (c) 2017 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 3.0.0
 * @uses       application/Config/http_interfaces.ini
 */

namespace HTTP;

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * Routing class
 *
 * @author David A. <tokernel@gmail.com>
 */
class Routing extends \Kernel\Base\Routing {

    /**
     * Parse HTTP Interface by URL.
     *
     * @access public
     * @throws ErrorException
     * @param string $url
     * @return array
     */
	public static function parseHttpInterface($url) {
		
		$config = new \Lib\Ini(TK_APP_PATH . 'Config' . TK_DS . 'http_interfaces.ini');
		
		// Data to return
		$result = array(
			'interface' => null,
			'request' => array(
				'interface_name' => 'tokernel_default'
			)
		);
		
		// Initialize default interface.
		$interface = $config->getSection('tokernel_default');
				
		foreach($config->getSectionNames() as $interfaceName) {
			
			// Skip default interface
			if($interfaceName == 'tokernel_default') {
				continue;
			}
			
			// Check if interface is enabled.
			if(!$config->getItem('enabled', $interfaceName)) {
				continue;
			}
			
			$interfacePattern = $config->getItem('pattern', $interfaceName);
			
			// Check if pattern is empty
			if($interfacePattern == '') {
				throw new ErrorException('Interface ['.$interfaceName.'] pattern cannot be empty!');
			}
			
			$pregPattern = preg_quote($interfacePattern, '#');
			$pregPattern = str_replace('\*', '.*', $pregPattern);
			
			// Return interface name if pattern matches.
			if(preg_match('#^'. $pregPattern .'$#', $url) === 1) {
				
				// Check if matched interface inherited from any
				$inherited = $config->getItem('inherited', $interfaceName);
				
				if($inherited != '') {
					// Merge Interfaces
					$interface = array_merge(
						$interface,
						$config->getSection($inherited)
					);
				}
				
				// Merge interface with default.
				$interface = array_merge(
					$interface,
					$config->getSection($interfaceName)
				);
				
				$result['request']['interface_name'] = $interfaceName;
				
				// Interface matches, Break the loop.
				break;
			}
				
		}
		
		unset($config);
		
		// First Initialization stage is complete
		$result['request']['url'] = $url;
		$result['interface'] = $interface;

		// Parse and define URL parts
		$result = self::parseInterfaceUrl($result);

		// After URL Initialization, let's parse the language and split the URL parts/params
		$result = self::parseLanguageAndParams($result);
		
		// Parse The interface Addon/Action
		$result = self::parseAddonAction($result);
		
		// Return interface as array
		return $result;
		
	} // End func parseHttpInterface

    /**
     * Parse Interface Url
     *
     * @access private
     * @param array $data
     * @return mixed
     */
	private static function parseInterfaceUrl($data) {
		
		$url = $data['request']['url'];
		$pattern = $data['interface']['pattern'];
		
		$data['request']['https'] = 0;
		$data['request']['subdomains'] = array();
		$data['request']['hostname'] = '';
		$data['request']['url_parts'] = '';
		$data['request']['url_params'] = '';
		$data['request']['interface_path'] = '';
		$data['request']['base_url'] = '';
		
		// Define HTTPS
		if(isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] != 'off') {
			$data['request']['https'] = 1;
		}
		
		// Define hostname
		$pos = strpos($url, '/');
		
		if($pos !== false) {
			
			$data['request']['hostname'] = substr($url, 0, $pos);
			
			// Define URL Parts
			$data['request']['url_parts'] = trim(substr($url, $pos), '/');
						
		} else {
			$data['request']['hostname'] = $url;
		}

		// Parse sub-domain(s) if not an IP address.
		if(! \Lib\Valid::ipAddress($data['request']['hostname'])) {
			$data['request']['subdomains'] = explode('.', $data['request']['hostname']);
		}
		
		// Define base url if not defined
		if($data['interface']['base_url'] == '') {
			
			if($data['request']['https'] == true) {
				$data['request']['base_url'] = 'https://';
			} else {
				$data['request']['base_url'] = 'http://';
			}
			
			$data['request']['base_url'] .= $data['request']['hostname'];
			
			$build_base_url = true;

		} else {
			$data['request']['base_url'] = $data['interface']['base_url'];
			$build_base_url = false;
		}

		// No more things to parse
		if($data['request']['url_parts'] == '' or $pattern == '') {
			$data['request']['url_params'] = $data['request']['url_parts'];
			return $data;
		}

		// Define other parts
		$pos = strpos($pattern, '/');

		if($pos === false) {
			$data['request']['url_params'] = $data['request']['url_parts'];
			return $data;
		}

		$interfacePath = substr($pattern, $pos);
        $interfacePath = str_replace('*', '', $interfacePath);
		$data['request']['interface_path'] = $interfacePath;

		// Add Interface path to base URL
		if($build_base_url == true) {
			$data['request']['base_url'] .= $data['request']['interface_path'];
		}

		// Important! This will allow to get Params accurate.
        $interfacePath = trim($interfacePath, '/');

		// Define Position of Interface
		$pos = strpos($data['request']['url_parts'], $interfacePath);
        $pos = $pos + strlen($interfacePath);

		$urlParams = substr($data['request']['url_parts'], $pos);

		$data['request']['url_params'] = trim($urlParams, '/');

		return $data;
		
	} // End func parseInterfaceUrl

    /**
     * Explode/Parse URL String to Array
     *
     * @access private
     * @param string $url
     * @return array
     */
	private static function explodeToArray($url) {
		
		if(empty($url)) {
			return array();
		}
		
		$arr = explode('/', $url);
		$ret_arr = array();
		
		foreach($arr as $index => $value) {
			if(trim($value) != '') {
				$ret_arr[] = $value;
			}
		}

		return $ret_arr;

	} // End func explodeToArray

    /**
     * Parse Language from URL and Parameters
     *
     * @access private
     * @param array $data
     * @return array
     */
	private static function parseLanguageAndParams($data) {

		// Explode parameters to array
		$data['request']['url_parts'] = self::explodeToArray($data['request']['url_parts']);
		$data['request']['url_params'] = self::explodeToArray($data['request']['url_params']);

		// Set Default language prefix.
		$data['request']['language_prefix'] = $data['interface']['default_language'];
		
		// Define Allowed languages array for check.
		$data['request']['allowed_languages'] = explode('|', $data['interface']['allowed_languages']);
		
		// First check, if url parameters is empty, then define language prefix and return interface.
		if(empty($data['request']['url_params'])) {
			$data['request']['language_prefix'] = self::catchIfUserAgentLanguage($data);
			return $data;
		}
		
		// Check, if parsing language from URL is not enabled.
		if(!$data['interface']['parse_url_language']) {
			$data['request']['language_prefix'] = self::catchIfUserAgentLanguage($data);
			return $data;
		}
		
		// Check, if allowed to catch 
		if(in_array($data['request']['url_params'][0], $data['request']['allowed_languages'])) {
			$data['request']['language_prefix'] = array_shift($data['request']['url_params']);
		} else {
			$data['request']['language_prefix'] = $data['interface']['default_language'];
		}
		
		return $data;
		
	} // End func parseLanguageAndParams

    /**
     * Catch language from User Agent if specified
     *
     * @access private
     * @param array $data
     * @return string
     */
	private static function catchIfUserAgentLanguage($data) {
		
		// Not allowed to catch browser language.
		// Just set Default language of interface.
		if($data['interface']['catch_user_agent_language'] != 1) {
			return $data['interface']['default_language'];
		}
		
		$userAgentLanguages = \Lib\Client::languages();
		
		if(empty($userAgentLanguages)) {
			return $data['interface']['default_language'];
		}
		
		foreach($userAgentLanguages as $language) {
			
			$tmp = explode('-', $language);
			$prefix = $tmp[0];
			
			if(in_array($prefix, $data['request']['allowed_languages'])) {
				return $prefix;
			}
			
		}
		
		return $data['interface']['default_language'];

	} // End func catchIfUserAgentLanguage

    /**
     * Parse requested Addon and Action names.
     *
     * @access private
     * @param array $data
     * @return array
     */
	private static function parseAddonAction($data) {
		
		// Set defaults
		$data['request']['addon'] = $data['interface']['default_callable_addon'];
		$data['request']['action'] = $data['interface']['default_callable_action'];
			
		if(empty($data['request']['url_params'])) {
			return $data;
		}

		$data = array_merge($data, self::parseRoute($data));
		
		$data['request']['addon'] = array_shift($data['request']['url_params']);
			
		if(!empty($data['request']['url_params'])) {
			$data['request']['action'] = array_shift($data['request']['url_params']);
		}
		
		$data['request']['addon'] = strtolower($data['request']['addon']);
		$data['request']['action'] = strtolower($data['request']['action']);
		
		/*
		 * Check, if application allowed to parse URLs with dashed segments.
		 * Example: /addon-name-with-dashes/and-action-name/param-1/param-2
		 * Will parse as:
		 * addon: addon_name_with_dashes
		 * action: and_action_name
		 * url_params: param-1, param-2
		 * Notice: in routes configuration dashes is allowed by default.
		 */
		if($data['interface']['allow_url_dashes'] == 1) {
			$data['request']['addon'] = str_replace('-', '_', $data['request']['addon']);
			$data['request']['action'] = str_replace('-', '_', $data['request']['action']);
		}
		
		return $data;

	} // End func parseAddonAction
	
	/**
	 * Parse route
	 *
	 * @static
	 * @access private
	 * @param array $data
	 * @return array
	 */
	private static function parseRoute($data) {
		
		$q_arr = $data['request']['url_params'];
		
		$data['request']['route_parsed'] = '';
				
		$routesIni = new \Lib\Ini(TK_APP_PATH . 'Config' . TK_DS . 'routes.ini');
				
		// Check if this interface inherited another (excepts tokernel_default).
		if($data['interface']['inherited'] != '' and $data['interface']['inherited'] != 'tokernel_default') {
			$parentInterfaceRoutes = $routesIni->getSection($data['interface']['inherited']);
		} else {
			$parentInterfaceRoutes = array();
		}
		
		// Load actual Routes for interface
		$routes = $routesIni->getSection($data['request']['interface_name']);
				
		if(empty($routes)) {
			$routes = array();
		}
		
		// Merge if any.
		$routes = array_merge($parentInterfaceRoutes, $routes);
		
		// Now we have to check, if interface routes not empty
		if(empty($routes)) {
			return $data;
		}

		// Parse each route to detect matching
		foreach($routes as $item => $value) {
			
			$r_arr = explode('/', trim($item, '/'));
			$v_arr = explode('/', trim($value, '/'));
			
			$nqs = self::compareRoute($q_arr, $r_arr, $v_arr);
			
			if($nqs !== false) {
				
				$data['request']['route_parsed'] = $item.'='.$value;
				$data['request']['url_params_orig'] = $data['request']['url_params'];
				$data['request']['url_params'] = $nqs;
				
				return $data;
			}
			
		}
				
		unset($routesIni);
		
		return $data;
		
	} // End func parseRoute
	
} /* End of class Routing */