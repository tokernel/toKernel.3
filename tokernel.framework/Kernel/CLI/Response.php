<?php
/**
 * toKernel by David A. and contributors.
 * Main Response class for CLI (Command line interface) mode.
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
 * Response class
 *
 * @author David A. <tokernel@gmail.com>
 */
class Response {

    /**
     * Status of this class initialization
     *
     * @access private
     * @staticvar bool
     */
    private static $initialized = false;

	/**
	 * Colored text output will be enabled related to OS.
	 *
	 * @access protected
	 * @var bool
	 */
	protected static $coloredOutput = false;
	
	/**
	 * Foreground colors for CLI output
	 *
	 * @access protected
	 * @var array
	 */
	protected static $foreColors = array();
	
	/**
	 * Background colors for CLI output
	 *
	 * @access protected
	 * @var array
	 */
	protected static $backColors = array();

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
    public function __clone() {
        throw new ErrorException('Cloning the object is not permitted ('.__CLASS__.')', E_USER_ERROR );
    }

	/**
	 * Main Initialization method
	 *
	 * @final
     * @throws ErrorException
	 * @access public
	 */
	public static final function init() {

        // Request can be initialized only once.
        if(self::$initialized == true) {
            throw new ErrorException('Response initialization - ' . __CLASS__ . '->' . __FUNCTION__ . '() is already initialized!');
        }
				
		/* Detect OS for colored output */
		if(strtoupper(substr(PHP_OS, 0, 3)) != 'WIN') {
			
			self::$coloredOutput = true;
			
			/* Set CLI text output Colors */
			self::$foreColors['black'] = '0;30';
			self::$foreColors['dark_gray'] = '1;30';
			self::$foreColors['blue'] = '0;34';
			self::$foreColors['light_blue'] = '1;34';
			self::$foreColors['green'] = '0;32';
			self::$foreColors['light_green'] = '1;32';
			self::$foreColors['cyan'] = '0;36';
			self::$foreColors['light_cyan'] = '1;36';
			self::$foreColors['red'] = '0;31';
			self::$foreColors['light_red'] = '1;31';
			self::$foreColors['purple'] = '0;35';
			self::$foreColors['light_purple'] = '1;35';
			self::$foreColors['brown'] = '0;33';
			self::$foreColors['yellow'] = '1;33';
			self::$foreColors['light_gray'] = '0;37';
			self::$foreColors['white'] = '1;37';
			
			self::$backColors['black'] = '40';
			self::$backColors['red'] = '41';
			self::$backColors['green'] = '42';
			self::$backColors['yellow'] = '43';
			self::$backColors['blue'] = '44';
			self::$backColors['magenta'] = '45';
			self::$backColors['cyan'] = '46';
			self::$backColors['light_gray'] = '47';
			
		} // end if colored output enabled

        self::$initialized = true;
		
	} // End func init

	/**
	 * Output colored string to console (if colors enabled).
	 *
	 * @access public
	 * @param string $string
	 * @param string $foreColor = NULL
	 * @param string $backColor = NULL
	 * @param mixed  $outputEndOfLine = true
	 * @return void
	 */
	public static function output($string, $foreColor = NULL, $backColor = NULL, $outputEndOfLine = true) {
		
		// Output string to console without colors
		if(!self::$coloredOutput) {

		    // By default the text will be output with end of line.
			if($outputEndOfLine == true) {
				$string .= TK_NL;
			}
			
			fwrite(STDOUT, $string);
			return;
		}
		
		$coloredStringToOutput = '';
		
		// Check if given foreground color found
		if(!empty($foreColor)) {
			$coloredStringToOutput .= "\033[".self::$foreColors[$foreColor]."m";
		}
		
		// Check if given background color found
		if(!empty($backColor)) {
			$coloredStringToOutput .= "\033[" . self::$backColors[$backColor] . "m";
		}
		
		// Add string and end coloring
		$coloredStringToOutput .=  $string . "\033[0m";
		
		if($outputEndOfLine == true) {
			$coloredStringToOutput .= TK_NL;
		}
		
		// Output to console
		fwrite(STDOUT, $coloredStringToOutput);
		
	} // End func output
		
	/**
	 * Output CLI Usage message to console
	 *
	 * @access public
	 * @param string $additionalMessage
	 * @return void
	 */
	public static function outputUsage($additionalMessage = '') {
		
		// Output copyright info
        $message = '';

		$message .= TK_NL." -";
		$message .= TK_NL." | toKernel by David A. and contributors v".TK_VERSION;
		$message .= TK_NL." | Copyright (c) " . date('Y') . " toKernel <framework@tokernel.com>";
		$message .= TK_NL." | ";
		$message .= TK_NL." | Running in " . php_uname();
		$message .= TK_NL." - ";
		
		self::output($message, 'green');
		
		// Output additional message if not empty
		if($additionalMessage != '') {
			self::output(' ', null, null, false);
			self::output(' ' . $additionalMessage . ' ', 'white', 'red', false);
			self::output(' ', null, null, true);
		}
		
		// Output usage info
		$message = TK_NL;
		$message .= " Usage: /usr/bin/php " . TK_ROOT_PATH . 'index.php';
		
		$message .= " {addon_name}";
		$message .= " {action_name}";
		
		$message .= " [argument_1] [argument_N]";
		$message .= TK_NL;
		
		self::output($message, 'white');
		
	} // end func outputUsage
	
	/**
	 * Output All possible colors to console
	 *
	 * @access public
	 * @return void
	 */
	public static function outputColors() {
		
		if(empty(self::$foreColors)) {
			self::output(TK_NL);
			self::output("Unable to output colors. This Operating system doesn't support this feature.");
			self::output(TK_NL);
            exit(1);
		}
		
		self::output(TK_NL);
		self::output(' [Forecolors] ' . "\t" . '[Backcolors]', 'black', 'yellow', false);
		self::output(TK_NL);
		
		reset(self::$foreColors);
		reset(self::$backColors);
		
		ksort(self::$foreColors);
		ksort(self::$backColors);
		
		for($i = 0; $i < count(self::$foreColors); $i++) {

		    self::output(' '. key(self::$foreColors) . ' ', key(self::$foreColors), NULL, false);
			
			if(key(self::$backColors)) {
				$j = '';
				$val = 15 - strlen(key(self::$foreColors));
				for($k = 0; $k < $val; $k++) {
					$j .= ' ';
				}
				self::output(' ' . $j, NULL, NULL, false);
				self::output(' ' . key(self::$backColors) . ' ', 'black', key(self::$backColors), false);
			}
			
			self::output(TK_NL);
			next(self::$foreColors);
			next(self::$backColors);
		}
		
	} // end func outputColors
	
	/**
	 * Return text with colored format.
	 * If the OS where is application running is not *nix like, the method will return value as is.
	 *
	 * @access public
	 * @param string $string
	 * @param string $foreColor
	 * @param string $backColor
	 * @return string
	 */
	public static function getColoredString($string, $foreColor = NULL, $backColor = NULL) {
		
		// Return string without colors
		if(!self::$coloredOutput) {
			return $string;
		}
		
		$coloredString = '';
		
		/* Check if given foreground color found */
		if(isset(self::$foreColors[$foreColor]) and !empty($foreColor)) {
            $coloredString .= "\033[".self::$foreColors[$foreColor]."m";
		}
		
		/* Check if given background color found */
		if(isset(self::$backColors[$backColor]) and !empty($backColor)) {
            $coloredString .= "\033[" . self::$backColors[$backColor] . "m";
		}
		
		/* Add string and end coloring */
        $coloredString .=  $string . "\033[0m";
		
		return $coloredString;
		
	} // end func getColoredString
	
} // End class Response