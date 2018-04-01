<?php
/**
 * toKernel by David A. and contributors.
 * Main Response class for HTTP mode.
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
 * @uses       tokernel.framework/config/status_codes.ini
 */

namespace HTTP;

/* Restrict direct access to this file */
use Psr\Log\InvalidArgumentException;

defined('TK_EXEC') or die('Restricted area.');

/**
 * response class
 *
 * @author David A. <tokernel@gmail.com>
 */
class Response {
			
	/**
	 * Status code
	 *
	 * @var int
	 * @access protected
	 */
	private static $statusCode = 200;
	
	/**
     * Headers to output
     *
	 * @var array
	 * @access private
	 */
	private static $headers;
	
	/**
	 * Content to output as an array
     * Each time setting content will add to this array and combine to string before output.
	 *
	 * @var array
	 * @access private
	 */
	private static $outputContent = array();

    /**
     * Content to output as json
     * Each time setting content will replace previous one.
     *
     * @var mixed array | string
     * @access private
     */
    private static $outputJsonContent;

    /**
     * Template to parse
     *
     * @var string
     * @access private
     */
    private static $templateName;

    /**
     * Template Variables to parse
     *
     * @var array
     * @access private
     */
    private static $templateVars;

    /**
     * Content type
     *
     * @var string
     * @access private
     */
    private static $contentType;

    /**
     * Charset
     *
     * @var string
     * @access private
     */
    private static $charset;

    /**
     * Status if final output already sent
     *
     * @var bool
     * @access private
     */
    private static $outputSent = false;

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
	 * Trigger E_USER_ERROR if attempting to clone.
	 *
	 * @throws ErrorException
	 * @access public
	 * @return void
	 */
	public final function __clone() {
		throw new ErrorException('Cloning the object is not permitted ('.__CLASS__.')', E_USER_ERROR );
	}

    /**
     * Return status if final output sent
     *
     * @access public
     * @return bool
     */
    public static function outputSent() {
        return self::$outputSent;
    }

	/**
	 * Set status code
	 *
     * @throws \InvalidArgumentException
	 * @access public
	 * @param int $statusCode
	 * @return void
	 */
	public static function setStatusCode($statusCode) {

        if (!is_integer($statusCode) || $statusCode < 100 || $statusCode > 599) {
            throw new InvalidArgumentException('Invalid HTTP status code');
        }

        self::$statusCode = $statusCode;

	} // End func setStatusCode
	
	/**
	 * Set header
	 *
	 * @access public
	 * @param string $item
     * @param mixed $value
	 * @return void
	 */
	public static function setHeader($item, $value) {
		self::$headers[$item] = $value;
    } // End func setHeader

    /**
     * Set Content type
     *
     * @throws \InvalidArgumentException
     * @access public
     * @param string $contentType
     * @return void
     */
    public static function setContentType($contentType) {

        if($contentType == '') {
            throw new \InvalidArgumentException('Content type us empty!');
        }

        self::$contentType = $contentType;

    } // End func setContentType

    /**
     * Set Charset
     *
     * @access public
     * @param string $charset
     * @return void
     */
    public static function setCharset($charset) {
        self::$charset = $charset;
    } // End func setCharset
	
	/**
	 * Set output content.
     * This will replace exiting buffer.
	 *
	 * @access public
	 * @param mixed $content
	 * @return void
	 */
	public static function resetContent($content) {

	    self::$outputContent = array();
	    self::$outputContent[] = $content;

	} // End func resetContent

    /**
     * Set Json content to output.
     * This will replace previous one if already set.
     *
     * @throws \InvalidArgumentException
     * @access public
     * @param mixed string | array $jsonContent
     * @return void
     */
    public static function setJsonContent($jsonContent) {

        if(self::$contentType != 'application/json') {
            throw new \InvalidArgumentException('Output content is not Json!');
        }

        self::$outputJsonContent = $jsonContent;

    }

    /**
     * Append content to output
     *
     * @throws \InvalidArgumentException
     * @access public
     * @param string $content
     * @return void
     */
    public static function setContent($content) {

        if(self::$contentType == 'application/json') {
            throw new \InvalidArgumentException('Output content is Json!');
        }

        self::$outputContent[] = $content;
    }

	/**
	 * Final output of content
	 *
	 * @access public
     * @return void
     * @todo finalize this method!
	 */
	public static function output() {

	    // Prepare to compress output content, if the extension "zlib" is loaded

        if(extension_loaded('zlib') and isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            if(strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
                ob_start("ob_gzhandler");
            } else {
                ob_start();
            }
        }

        self::outputHeaders();

        $outputContent = self::getBody();

        if(self::$contentType == 'application/json') {

            echo $outputContent;

        } else {

            if(self::$templateName != '') {

                // Load template
                $templatesDirectory = \HTTP\Runner::getInterfaceConfig('web_templates');
                $templateFile = TK_APP_PATH . 'Templates' . TK_DS . $templatesDirectory . TK_DS . self::$templateName . '.tpl.php';

                $tpl = new \Lib\Template($templateFile, self::$templateVars);

                echo $tpl->getParsed($outputContent);

            } else {

                // Output content without template
                echo $outputContent;

            }
        }

        $finalOutput = ob_get_clean();

        echo $finalOutput;

        self::$outputSent = true;

        return $finalOutput;

	} // End func output

    /**
     * Build and return content body
     *
     * @access public
     * @return string
     */
    public static function getBody() {

        if(self::$contentType == 'application/json') {

            if(is_array(self::$outputJsonContent)) {
                return json_encode(self::$outputJsonContent, JSON_UNESCAPED_UNICODE);
            } else {
                return self::$outputJsonContent;
            }

        } else {

            if(empty(self::$outputContent)) {
                return '';
            }

            $outputContent = '';

            foreach (self::$outputContent as $contentPart) {
                $outputContent .= $contentPart;
            }

            return $outputContent;

        }

    } // End func getBody

    /**
     * Output headers
     *
     * @access public
     * @return void
     */
    public static function outputHeaders() {

        // Send header status by code
        header(self::getHeaderStatus());

        if(self::$charset == '') {
            self::$charset = \HTTP\Runner::getInterfaceConfig('default_charset');
        }

        if(self::$contentType != '' and self::$charset != '') {
            header('Content-Type: ' . self::$contentType . '; charset='.self::$charset);
        } elseif(self::$charset != '') {
            header('Content-Type: charset=' . self::$charset);
        } elseif(self::$contentType != '') {
            header('Content-Type: ' . self::$contentType);
        }

        // Send headers. Previously defined headers will be overridden if already defined.
        if(!empty(self::$headers)) {
            foreach(self::$headers as $item => $value) {
                header($item . ': ' . $value);
            }
        }

    } // End func outputHeaders

	/**
	 * Define and return Status by code for header
	 *
	 * @access private
	 * @return string
	 */
	private static function getHeaderStatus() {
		
		$statusMessage = '';
		
		// In case if status code is well known, we just defining here instead of load ini file from configuration.
		switch(self::$statusCode) {
			case 200:
				$statusMessage = 'OK';
				break;
			case 201:
				$statusMessage = 'Created';
				break;
			case 204:
				$statusMessage = 'No Content';
				break;
			case 301:
				$statusMessage = 'Moved Permanently';
				break;
			case 400:
				$statusMessage = 'Bad Request';
				break;
			case 401:
				$statusMessage = 'Unauthorized';
				break;
			case 403:
				$statusMessage = 'Forbidden';
				break;
			case 404:
				$statusMessage = 'Not Found';
				break;
			case 405:
				$statusMessage = 'Method Not Allowed';
				break;
			case 500:
				$statusMessage = 'Internal Server Error';
				break;
			case 503:
				$statusMessage = 'Service Unavailable';
				break;
			default:
				$statusMessage = self::getHeaderStatusByCode(self::$statusCode);
				break;
		}
				
		$phpSapiName  = substr(php_sapi_name(), 0, 3);
		
		if ($phpSapiName == 'cgi' || $phpSapiName == 'fpm') {
			return 'Status: '.self::$statusCode.' '.$statusMessage;
		} else {
			// Define Server Protocol.
			if(isset($_SERVER['SERVER_PROTOCOL'])) {
				$serverProtocol = $_SERVER['SERVER_PROTOCOL'];
			} else {
				$serverProtocol = 'HTTP/1.0';
			}
			return $serverProtocol.' '.self::$statusCode.' '.$statusMessage;
		}
		
	} // End func getHeaderStatus
	
	/**
	 * Load ini file and return status message by code
	 *
	 * @access public
	 * @throws \ErrorException
     * @param int $code
	 * @return string
	 */
	public static function getHeaderStatusByCode($code) {
		
		$statusesIni = new \Lib\ini(TK_PATH . 'config' . TK_DS . 'status_codes.ini');
		
		if(!$statusesIni->itemExists($code)) {
			throw new \ErrorException('Status code `'.$code.'` not exists in configuration file!');
		}
		
		return $statusesIni->getItem(self::$statusCode);

	} // End func getHeaderStatusByCode

    /**
     * Return Status code
     *
     * @access public
     * @return int
     */
    public static function getStatusCode() {
        return self::$statusCode;
    }

    /**
     * Set Template to parse
     *
     * @access public
     * @param string $templateName
     * @param array $templateVars
     * @return void
     */
    public static function setTemplate($templateName, $templateVars = array()) {
        self::$templateName = $templateName;
        self::$templateVars = $templateVars;
    }

    /**
     * Redirect
     *
     * @param string $url
     * @param mixed int | null $statusCode
     */
    public static function redirect($url, $statusCode = NULL) {

        if(!is_null($statusCode)) {
            self::setStatusCode($statusCode);
        } else {
            self::setStatusCode(self::$statusCode);
        }

        self::setHeader('Location', $url);

        exit();

    } // End func redirect

    /**
     * Download file
     *
     * @access public
     * @param string $fileName
     * @param mixed string | null $fileContent
     * @return null
     * @todo Finish this method
     */
    public static function downloadFile($fileName, $fileContent = NULL) {

        // @todo figure out this
        self::outputHeaders();

        // Download file with given content
        if(!is_null($fileContent)) {

        // Open file content from given path and download
        } else {

        }


    } // End func downloadFile

    /**
     * Sets Header to not caching
     *
     * @access public
     * @return void
     */
    public static function noCache() {
        self::setHeader('Cache-Control', 'no-store, max-age=0, no-cache');
    }
	
} // End class Response