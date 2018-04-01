<?php
/**
 * toKernel by David A. and contributors.
 * Main Request class for HTTP mode.
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

namespace HTTP;

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
	 * @staticvar boolean
	 */
	private static $initialized = false;

    /**
     * Request Parsed array
     *
     * @access private
     * @var array
     */
    private static $request;

	/**
	 * Deprecated globals, will removed.
	 *
	 * @access private
	 * @var array
	 */
	private static $globalsToRemove = array(
		'HTTP_ENV_VARS',
		'HTTP_POST_VARS',
		'HTTP_GET_VARS',
		'HTTP_COOKIE_VARS',
		'HTTP_SERVER_VARS',
		'HTTP_POST_FILES',
	);

    /**
     * Globals to clean
     *
     * @access private
     * @var array
     */
    private static $globalsToClean = array(
        '_POST',
        '_REQUEST',
        '_COOKIE',
        '_FILES',
        '_SERVER',
        '_SESSION',
        'argv'
    );
	
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
	 * @throws \ErrorException
	 * @param object $config
	 * @return bool
	 */
	public static function init($config) {

        // Request can be initialized only once.
		if(self::$initialized == true) {
			throw new \ErrorException('Request initialization - ' . __CLASS__ . '->' . __FUNCTION__ . '() is already initialized!');
		}

		// Initialize Client lib
		\Lib\Client::init();
		
		// Clean globals and XSS if enabled
		if($config->getItem('auto_clean_globals', 'HTTP') == 1) {

			/* Prevent malicious GLOBALS overload attack */
			if(isset($_REQUEST['GLOBALS']) or isset($_FILES['GLOBALS'])) {
				throw new \ErrorException('Global variable overload attack detected! Request aborted.');
			    exit(1);
			}
			
			/*
			* Remove globals which exists in removable globals,
			* otherwise do clean by received arguments.
			*/
			foreach($GLOBALS as $key => $value) {
				if(in_array($key, self::$globalsToRemove)) {
					unset($GLOBALS[$key]);
				}
			}
			
			// Define methods other than GET and POST
			$method = self::method();
			
			if($method != 'GET' and $method != 'POST') {
				
				$_GLOBAL_REQUEST_ = array();
				parse_str(file_get_contents('php://input'), $_GLOBAL_REQUEST_);
				$GLOBALS['_' . $method] = $_GLOBAL_REQUEST_;
				
				// Add these request vars into _REQUEST
				$_REQUEST = $_REQUEST + $_GLOBAL_REQUEST_;
				
			}
			
			// Reset $_GET
			$GLOBALS['_GET'] = array();

			// Clean globals
			foreach(self::$globalsToClean as $globalName) {
				
				if(isset($GLOBALS[$globalName])) {
					
					$GLOBALS[$globalName] = \Lib\Filter::cleanData($GLOBALS[$globalName]);
					
					// Clean for XSS
					if($config->getItem('auto_clean_globals_xss', 'HTTP') == 1) {
						$GLOBALS[$globalName] = \Lib\Filter::cleanXss($GLOBALS[$globalName], false);
					}
					
				} else {
					$GLOBALS[$globalName] = array();
				}
				
			}
			
		}
				
		// Clean URL String if enabled
		if($config->getItem('auto_clean_url', 'HTTP') == 1) {

			if(isset($_SERVER['HTTP_HOST'])) {
				
				// Ensure hostname only contains characters allowed in hostname.
				$_SERVER['HTTP_HOST'] = strtolower($_SERVER['HTTP_HOST']);
				
				if(! \Lib\Valid::httpHost($_SERVER['HTTP_HOST'])) {
					throw new \ErrorException('Invalid HTTP_HOST `'.$_SERVER['HTTP_HOST'].'`!');
				}
				
			} else {
				$_SERVER['HTTP_HOST'] = '';
			}
			
			// Clean some globals before using
			
			$_SERVER['REQUEST_URI']  = \Lib\Filter::cleanData($_SERVER['REQUEST_URI']);
			$_SERVER['REQUEST_URI']  = \Lib\Filter::cleanXss($_SERVER['REQUEST_URI'], true);
			$_SERVER['QUERY_STRING'] = \Lib\Filter::cleanData($_SERVER['QUERY_STRING']);
			$_SERVER['QUERY_STRING'] = \Lib\Filter::cleanXss($_SERVER['QUERY_STRING'], true);
			
			if(isset($_SERVER['REDIRECT_URL'])) {
				$_SERVER['REDIRECT_URL'] = \Lib\Filter::cleanData($_SERVER['REDIRECT_URL']);
				$_SERVER['REDIRECT_URL'] = \Lib\Filter::cleanXss($_SERVER['REDIRECT_URL'], true);
			}
			
			if(isset($_SERVER['REDIRECT_QUERY_STRING'])) {
				$_SERVER['REDIRECT_QUERY_STRING'] = \Lib\Filter::cleanData($_SERVER['REDIRECT_QUERY_STRING']);
				$_SERVER['REDIRECT_QUERY_STRING'] = \Lib\Filter::cleanXss($_SERVER['REDIRECT_QUERY_STRING'], true);
			}
			
			if(isset($_SERVER['argv'][0])) {
				$_SERVER['argv'][0] = \Lib\Filter::cleanData($_SERVER['argv'][0]);
				$_SERVER['argv'][0] = \Lib\Filter::cleanXss($_SERVER['argv'][0], true);
			}

			$_SERVER['QUERY_STRING'] = trim($_SERVER['QUERY_STRING']);
		}
		
		// Initialize interface configuration
		$parsedResult = Routing::parseHttpInterface($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
		self::$request = $parsedResult['request'];
        self::$initialized = true;

        // This should be used by Runner class.
		return $parsedResult['interface'];
				
	} // End func init

	/**
	 * Return subdomain if exists in host name.
	 *
	 * @access public
	 * @return mixed string | bool
	 */
	public static function subdomain($item = NULL) {
		
		if(is_null($item)) {
			return self::$request['subdomains'];
		}
		
		if(isset(self::$request['subdomains'][$item])) {
			return self::$request['subdomains'][$item];
		}
		
		return false;
		
	} // End func subdomain
	
	/**
	 * Return detected language prefix
	 *
	 * @access public
	 * @return string
	 */
	public static function languagePrefix() {
		return self::$request['language_prefix'];
	}
			
	/**
	 * Return base url
	 *
	 * @access public
	 * @return string
	 */
	public static function baseUrl() {
		
		$baseUrl = '';
		
		if(self::isHttps()) {
			$baseUrl .= 'https://';
		} else {
			$baseUrl .= 'http://';
		}
		
		$baseUrl .= self::$request['hostname'] . '/';
				
		if(self::$request['interface_path'] != '') {
			$baseUrl .= self::$request['interface_path'] . '/';
		}

		if(self::$interface['parse_url_language'] == 1) {
		    $baseUrl .= self::languagePrefix() . '/';
        }

		return $baseUrl;

	} // End func baseUrl
	
	public static function url() {
		return self::$request['url'];
	}
	
	/**
	 * Return requested/default addon
	 *
	 * @access public
	 * @return string
	 */
	public static function addon() {
		return self::$request['addon'];
	}
	
	/**
	 * Return requested/default action
	 *
	 * @access public
	 * @return string
	 */
	public static function action() {
		return self::$request['action'];
	}
		
	/**
	 * Return true if the request protocol is https
	 *
	 * @access public
	 * @return bool
	 */
	public static function isHttps() {
		
		if(self::server('HTTPS') and self::server('HTTPS') != 'off') {
			return true;
		} else {
			return false;
		}
		
	} // End func isHttps
		
	/**
	 * Return exploded parts from url
	 *
	 * @access public
	 * @param int $index
	 * @return mixed
	 */
	public static function urlParts($index = NULL) {
		
		if(is_null($index)) {
			return self::$request['url_parts'];
		}
		
		if(isset(self::$request['url_parts'][$index])) {
			return self::$request['url_parts'][$index];
		}
		
		return false;
		
	} // End func urlParts
	
	/**
	 * Return count of url parts
	 *
	 * @access public
	 * @return integer
	 */
	public static function urlPartsCount() {
		return count(self::$request['url_parts']);
	}
	
	/**
	 * Return URL parameter value by index starting from 0 or all URL parameters array
	 *
	 * @access public
	 * @param string $index
	 * @return mixed
	 */
	public static function urlParams($index = NULL) {
		
		if(is_null($index)) {
			return self::$request['url_params'];
		}
		
		if(isset(self::$request['url_params'][$index])) {
			return self::$request['url_params'][$index];
		}
		
		return false;
		
	} // end func urlParams
	
	/**
	 * Return count of Url parameters
	 *
	 * @access public
	 * @return integer
	 */
	public static function urlParamsCount() {
		return count(self::$request['url_params']);
	}

    /**
     * Return requested interface name or empty string if not match.
     *
     * @access public
     * @return string
     */
	public static function interfaceName() {
		return self::$request['interface_name'];
	}

	/**
     * Return request method name
     *
     * @access public
     * @return string
	 */
	public static function method() {

	    if(isset($_SERVER['REQUEST_METHOD'])) {
			return $_SERVER['REQUEST_METHOD'];
		}

		return false;

	} // End func method

    /**
     * Return true, if the request is Ajax.
     *
     * @access public
     * @return bool
     */
	public static function isAjax() {
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
	}

	/**
     * Return Request data: POST/PUT/DELETE, etc...
     *
     * @param mixed $item
     * @param bool $encodeHtmlEntities
     * @param bool $cleanXss
     * @param bool $stripTags
     * @return mixed
     */
	public static function request($item = NULL, $encodeHtmlEntities = true, $cleanXss = false, $stripTags = false) {
		return self::getGlobals('_REQUEST', $item, $encodeHtmlEntities, $cleanXss, $stripTags);
	}
	
	/**
	 * Return GLOBAL _REQUEST data
	 *
	 * @access protected
     * @throws \ErrorException
	 * @param string $globalIndex
	 * @param mixed $item = NULL
	 * @param bool $encodeHtmlEntities = true
	 * @param bool $cleanXss = false
	 * @param bool $stripTags = false
	 * @return mixed
	 */
	protected static function getGlobals($globalIndex, $item = NULL, $encodeHtmlEntities = true, $cleanXss = false, $stripTags = false) {

	    // Requested Global Array not exists, such as _REQUEST, _POST, etc...
		if(!isset($GLOBALS[$globalIndex])) {
			throw new \ErrorException($globalIndex . ' not defined.');
		}
		
		// Requested Item not exists
		if(!is_null($item) and !isset($GLOBALS[$globalIndex][$item])) {
			return NULL;
		}
		
		if(is_null($item)) {
			// Return array
			$data = $GLOBALS[$globalIndex];
		} else {
			// Return item from array
			$data = $GLOBALS[$globalIndex][$item];
		}
		
		if($cleanXss == true) {
			$data = \Lib\Filter::cleanXss($data, $stripTags);
		} elseif ($stripTags == true) {
			$data = \Lib\Filter::stripTags($data);
		}
		
		if($encodeHtmlEntities == true) {
			$data = \Lib\Filter::encodeHtmlEntities($data);
		}
		
		return $data;
		
	} // End func getGlobals
		
	/**
	 * Return cleaned _COOKIE array or array item
	 *
	 * @access public
	 * @param mixed $item = NULL
	 * @param bool $encodeHtmlEntities = true
	 * @param bool $cleanXss = false
	 * @param bool $stripTags = false
	 * @return mixed
	 */
	public static function cookie($item = NULL, $encodeHtmlEntities = true, $cleanXss = false, $stripTags = false) {
		return self::getGlobals('_COOKIE', $item, $encodeHtmlEntities, $cleanXss, $stripTags);
	} // end func cookie
	
	/**
	 * Return cleaned _FILES array or array item
	 *
	 * @access public
	 * @param mixed $item = NULL
	 * @param bool $encodeHtmlEntities = true
	 * @param bool $cleanXss = false
	 * @param bool $stripTags = false
	 * @return mixed
	 */
	public static function files($item = NULL, $encodeHtmlEntities = true, $cleanXss = false, $stripTags = false) {
		return self::getGlobals('_FILES', $item, $encodeHtmlEntities, $cleanXss, $stripTags);
	} // end func files
	
	/**
	 * Return cleaned _SERVER array or array item
	 *
	 * @access public
	 * @param mixed $item = NULL
	 * @param bool $encodeHtmlEntities = true
	 * @param bool $cleanXss = false
	 * @param bool $stripTags = false
	 * @return mixed
	 */
	public static function server($item = NULL, $encodeHtmlEntities = true, $cleanXss = false, $stripTags = false) {
		return self::getGlobals('_SERVER', $item, $encodeHtmlEntities, $cleanXss, $stripTags);
	} // end func server
	
	/**
	 * Return cleaned _SESSION array or array item
	 *
	 * @access public
	 * @param mixed $item = NULL
	 * @param bool $encodeHtmlEntities = true
	 * @param bool $cleanXss = false
	 * @param bool $stripTags = false
	 * @return mixed
	 */
	public static function session($item = NULL, $encodeHtmlEntities = true, $cleanXss = false, $stripTags = false) {
		return self::getGlobals('_SESSION', $item, $encodeHtmlEntities, $cleanXss, $stripTags);
	} // end func server
	
	/**
	 * Return cleaned argv array or array item
	 *
	 * @access public
	 * @param mixed $item = NULL
	 * @param bool $encodeHtmlEntities = true
	 * @param bool $cleanXss = false
	 * @param bool $stripTags = false
	 * @return mixed
	 */
	public static function argv($item = NULL, $encodeHtmlEntities = true, $cleanXss = false, $stripTags = false) {
		return self::getGlobals('argv', $item, $encodeHtmlEntities, $cleanXss, $stripTags);
	} // end func server

    /**
     * Get Request headers
     *
     * @access public
     * @param mixed string | null $header
     * @return mixed array | string | NULL
     */
    public static function getHeaders($header = NULL) {

        $headers = getallheaders();

        if(is_null($header)) {
            return $headers;
        }

        foreach ($headers as $item => $value) {
            if($item == $header) {
                return $value;
            }
        }

        return NULL;

    } // End func getHeaders
	
} // End class Request