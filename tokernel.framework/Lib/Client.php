<?php
/**
 * toKernel by David A. and contributors.
 * Client's OS, Browser, Mobile device information collector class.
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
 * @category    library
 * @package     framework
 * @subpackage  library
 * @author      toKernel development team <framework@tokernel.com>
 * @copyright   Copyright (c) 2017 toKernel
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version     1.0.0
 * @link        http://www.tokernel.com
 * @since       File available since Release 3.0.0
 * @uses        tokernel.framework/Config/platforms.ini
 */

namespace Lib;

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * Client class
 *
 * @author David A. <tokernel@gmail.com>
 */
class Client {
		
	/**
	 * Platforms/Browsers configuration object
	 *
	 * @access protected
	 * @var object
	 */
	protected static $iniObject;
	
	/**
	 * Client data
	 *
	 * @access protected
	 * @var string
	 */
	protected static $client;
	
	/**
	 * Client platform name
	 *
	 * @access protected
	 * @var string
	 */
	protected static $platformName = 'Unknown Platform';
	
	/**
	 * Is browser accessed
	 *
	 * @access protected
	 * @var bool
	 */
	protected static $isBrowser = false;

	/**
	 * Browser
	 *
	 * @access protected
	 * @var string
	 */
	protected static $browserName = '';

    /**
     * Browser version
     *
     * @access protected
     * @var string
     */
    protected static $browserVersion = '';
	
	/**
	 * Is mobile accessed
	 *
	 * @access protected
	 * @var bool
	 */
	protected static $isMobile = false;
	
	/**
	 * Mobile name
	 *
	 * @access protected
	 * @var string
	 */
	protected static $mobileName = '';
	
	/**
	 * Accepted character sets
	 *
	 * @access protected
	 * @var array
	 */
	protected static $charsets;
	
	/**
	 * Accepted languages
	 *
	 * @access protected
	 * @var array
	 */
	protected static $languages;
	
	/**
	 * Is robot
	 *
	 * @access protected
	 * @var bool
	 */
	protected static $isRobot = false;
	
	/**
	 * Robot name
	 *
	 * @access protected
	 * @var string
	 */
	protected static $robotName = '';

    /**
     * Initialization status
     *
     * @access protected
     * @var bool
     */
	protected static $initialized = false;
	
	/**
	 * Initialize client data
	 *
	 * @access protected
	 * @return bool
	 */
	public static function init() {
		
		if(self::$initialized) {
			return true;
		}

		/* If running in CLI mode, define OS only */
		if(TK_RUN_MODE == 'cli') {
			self::$platformName = PHP_OS;
			return true;
		}
		
		if(isset($_SERVER['HTTP_USER_AGENT'])) {
			self::$client = trim($_SERVER['HTTP_USER_AGENT']);
		} else {
			return false;
		}
		
		/* Load platforms.ini file to ini object */
		self::$iniObject = new \Lib\Ini(TK_PATH . 'Config' . TK_DS . 'platforms.ini');
				
		self::definePlatform();
		self::defineBrowser();
		self::defineRobot();
		
		self::$initialized = true;

		return true;
		
	} // End func init
	
	/**
	 * Define platform
	 *
	 * @access protected
	 * @return bool
	 */
	protected static function definePlatform() {
		
		$platforms = self::$iniObject->getSection('PLATFORMS');

		if(!is_array($platforms)) {
			return false;
		}
		
		foreach($platforms as $key => $val) {
			if (preg_match("|" . preg_quote($key) . "|i", self::$client)) {
				self::$platformName = $val;
				return true;
			}
		}

		return false;
		
	} // End func definePlatform
	
	/**
	 * Define Browser
	 *
	 * @access protected
	 * @return bool
	 */
	protected static function defineBrowser() {
		
		$browsers = self::$iniObject->getSection('BROWSERS');
		
		if(!is_array($browsers)) {
			return false;
		}
		
		foreach($browsers as $key => $val) {
			if (preg_match("|" . preg_quote($key) . ".*?([0-9\.]+)|i", self::$client, $match)) {
				self::$isBrowser = true;
				self::$browserVersion = $match[1];
				self::$browserName = $val;
				self::defineMobile();
				return true;
			}
		}
		
		return false;
		
	} // End func defineBrowser
	
	/**
	 * Define Mobile
	 *
	 * @access protected
	 * @return bool
	 */
	protected static function defineMobile() {
		
		$mobiles = self::$iniObject->getSection('MOBILES');
		
		if(!is_array($mobiles)) {
			return false;
		}
		
		foreach($mobiles as $key => $val) {
			if (false !== (strpos(strtolower(self::$client), $key))) {
				self::$isMobile = true;
				self::$mobileName = $val;
				return true;
			}
		}
		
		return false;
		
	} // End func defineMobile
	
	/**
	 * Define accepted character sets
	 *
	 * @access protected
	 * @return void
	 */
	protected static function defineCharsets() {
		
		if(isset($_SERVER['HTTP_ACCEPT_CHARSET']) and $_SERVER['HTTP_ACCEPT_CHARSET'] != '') {
			
			$charsets = preg_replace('/(;q=.+)/i', '', strtolower(trim($_SERVER['HTTP_ACCEPT_CHARSET'])));
			self::$charsets = explode(',', $charsets);
			
		} else {
			self::$charsets = array();
		}
		
	} // End func defineCharsets
	
	/**
	 * Define accepted languages
	 *
	 * @access protected
	 * @return void
	 */
	protected static function defineLanguages() {
		
		if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) and $_SERVER['HTTP_ACCEPT_LANGUAGE'] != '') {
			
			$languages = preg_replace('/(;q=[0-9\.]+)/i', '', strtolower(trim($_SERVER['HTTP_ACCEPT_LANGUAGE'])));
			self::$languages = explode(',', $languages);
			
		} else {
			self::$languages = array();
		}
		
	} // End func defineLanguages
	
	/**
	 * Define Robot
	 *
	 * @access protected
	 * @return bool
	 */
	protected static function defineRobot() {
		
		$robots = self::$iniObject->getSection('ROBOTS');
		
		if(!is_array($robots)) {
			return false;
		}
		
		foreach ($robots as $key => $val) {
			if (preg_match("|" . preg_quote($key) . "|i", self::$client)) {
				self::$isRobot = true;
				self::$robotName = $val;
				return true;
			}
		}
		
		return false;
		
	} // End func defineRobot
	
	/**
	 * Return true if client access vie browser
	 *
	 * @access public
	 * @return bool
	 */
	public static function isBrowser() {
		return self::$isBrowser;
	}
	
	/**
	 * Return is mobile
	 *
	 * @access public
	 * @return bool
	 */
	public static function isMobile() {
		return self::$isMobile;
	}
	
	/**
	 * Return is Robot
	 *
	 * @access public
	 * @return bool
	 */
	public static function isRobot() {
		return self::$isRobot;
	}
	
	/**
	 * Check is Accept language exists
	 *
	 * @access public
	 * @return bool
	 */
	public static function isAcceptLanguage($lng = 'en') {
		
		$lng = strtolower($lng);
		
		if(in_array($lng, self::languages(), true) == true) {
			return true;
		}
		
		return false;
		
	} // End func isAcceptLanguage
	
	/**
	 * Check is Accept character set exists
	 *
	 * @access public
	 * @param string $charset = 'utf-8'
	 * @return bool
	 */
	public static function isAcceptCharset($charset = 'utf-8') {
		
		$charset = strtolower($charset);
		
		if(in_array($charset, self::charsets(), true) == true) {
			return true;
		}
		
		return false;
		
	} // End func  isAcceptCharset
	
	/**
	 * Return defined platform name
	 *
	 * @access public
	 * @return string
	 */
	public static function platformName() {
        return self::$platformName;
	}
	
	/**
	 * Return client browser name
	 *
	 * @access public
	 * @return string
	 */
	public static function browserName() {
		return self::$browserName;
	}
	
	/**
	 * Return defined browser version
	 *
	 * @access public
	 * @return string
	 */
	public static function browserVersion() {
		return self::$browserVersion;
	}
	
	/**
	 * Return defined mobile
	 *
	 * @access public
	 * @return string
	 */
	public static function mobile() {
		return self::$mobileName;
	}
	
	/**
	 * Return Robot name
	 *
	 * @access public
	 * @return string
	 */
	public static function robotName() {
		return self::$robotName;
	}
	
	/**
	 * Return accepted Character Sets
	 *
	 * @access	public
	 * @return	array
	 */
	public static function charsets() {
		
		if (!is_array(self::$charsets)) {
			self::defineCharsets();
		}
		
		return self::$charsets;
		
	} // End func charsets
	
	/**
	 * Return accepted languages
	 *
	 * @access	public
	 * @return	array
	 */
	public static function languages() {
		
		if (count(self::$languages) == 0) {
			self::defineLanguages();
		}
		
		return self::$languages;
		
	} // End func languages
	
	/**
	 * Return User Agent as a string
	 *
	 * @access public
	 * @return string
	 */
	public static function userAgent() {
		return self::$client;
	}
	
	/**
	 * Return array of defined client information
	 *
	 * @access public
	 * @return array
	 */
	public static function clientDump() {
		
		$c_arr = array();
		
		$c_arr['platform_name'] = self::platformName();
		$c_arr['is_browser'] = self::isBrowser();
		$c_arr['browser_name'] = self::browser();
		$c_arr['browser_version'] = self::browserVersion();
		$c_arr['is_mobile'] = self::isMobile();
		$c_arr['mobile_name'] = self::mobile();
		$c_arr['is_robot'] = self::isRobot();
		$c_arr['robot_name'] = self::robotName();
		$c_arr['charsets'] = self::charsets();
		$c_arr['languages'] = self::languages();
		$c_arr['user_agent'] = self::userAgent();
		
		return $c_arr;
		
	} // End func clientDump
	
	/**
	 * Return string of defined client information
	 *
	 * @access public
	 * @return string
	 */
	public static function clientInfo() {
		
		$buf = 'Client Information' . TK_NL;
		
		$c_arr = self::client_arr();
		
		foreach($c_arr as $key => $value) {
			
			if(is_array($value) == true) {
				$value = implode(', ', $value);
			}
			
			if(is_bool($value) == true) {
				if($value == true) {
					$value = 'Yes';
				} else {
					$value = 'No';
				}
			}
			
			$key = ucfirst($key);
			$key = str_replace('_', ' ', $key);
			
			$buf .= $key . ' : ' . $value . TK_NL;
			
		} // End foreach client values
		
		return $buf;
		
	} // End func clientInfo
	
	/**
	 * Return Client IP Address
	 *
	 * @access public
	 * @return string
	 */
	public static function ipAddress() {
		
		$ip = '';
		
		// Case 1. Get from HTTP_X_FORWARDED_FOR
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) and $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
			
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			
			if(strpos($ip, ',')) {
				$exp_ip = explode(',', $ip);
				$ip = $exp_ip[0];
			}

		// Case 2. Get from HTTP_CLIENT_IP
		} elseif(isset($_SERVER['HTTP_CLIENT_IP']) and $_SERVER['HTTP_CLIENT_IP'] != '') {
			$ip = $_SERVER['HTTP_CLIENT_IP'];

        // Case 3. Get from REMOTE_ADDR
		} elseif(isset($_SERVER["REMOTE_ADDR"])) {
			$ip = $_SERVER["REMOTE_ADDR"];
		}
				
		return $ip;
		
	} // End func ipAddress
	
} /* End of class Client */