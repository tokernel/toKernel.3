<?php
/**
 * toKernel by David A. and contributors.
 * Data filtering class library.
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
 * @category   library
 * @package    framework
 * @subpackage library
 * @author     David A. <framework@tokernel.com>
 * @copyright  Copyright (c) 2017 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 3.0.0
 */

namespace Lib;

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * Filter class
 *
 * Data filtering class library.
 *
 * @author David A. <tokernel@gmail.com>
 */
class Filter {
			
	/**
	 * Removable expressions.
	 *
	 * @access private
	 * @var array
	 */
	private static $expressionsToReplaced = array(
		'document.cookie'	=> '',
		'document.write'	=> '',
		'.parentNode'		=> '',
		'.innerHTML'		=> '',
		'window.location'	=> '',
		'self.location'     => '',
		'-moz-binding'		=> '',
		'<!--'				=> '&lt;!--',
		'-->'				=> '--&gt;',
		'<![CDATA['			=> '&lt;![CDATA['
	);
	
	/**
	 * Removable patterns.
	 *
	 * @access private
	 * @var array
	 */
	private static $patternsToRemove = array(
		"#javascript\s*:#i"				=> '',
		"#expression\s*(\(|&\#40;)#i"	=> '',
		"#vbscript\s*:#i"				=> '',
		"#Redirect\s+302#i"				=> ''
	);
	
	/**
	 * Removable invisible chars.
	 *
	 * @access private
	 * @var array
	 */
	private static $charsToRemove = array(
		'/%0[0-8bcef]/',	// url encoded 00-08, 11, 12, 14, 15
		'/%1[0-9a-f]/',		// url encoded 16-31
		'/[\x00-\x08]/',	// 00-08
		'/\x0b/', '/\x0c/',	// 11, 12
		'/[\x0e-\x1f]/'		// 14-31
	);
			
	/**
	 * Clean data.
	 * if xss_clean is true this function will call cleanXss function.
	 *
	 * @access public
	 * @param mixed $data to clean
	 * @return mixed
	 */
	public static function cleanData($data) {
		
		/*
		 * Call this function recursively if $data argument is array.
		 */
		if(is_array($data)) {

		    $tmpArr = array();
			
			/* Clean keys also, if array is associative */
			if(\Lib\Arrays::isAssoc($data)) {
				foreach($data as $key => $value) {
					/* Clean key. pass only a-z, A-Z, 0-9, -, _, . chars */
					$key = self::stripChars($key, array('-', '_', '.'));
					/* Clean value */
					$tmpArr[$key] = self::cleanData($value);
				} // end foreach
			} else {
				foreach($data as $value) {
					$tmpArr[] = self::cleanData($value);
				} // end foreach
			} // end if array is assoc
			
			return $tmpArr;
			
		} elseif (is_string($data)) {
			
			$data = str_replace(array("\r\n", "\r"), "\n", $data);
			$data = trim($data);
			
			return $data;

		} else {

		    // Nothing to change in string
		    return $data;
        }
		
	} // end func cleanData
	
	/**
	 * Clean data for Cross Site Scripting Hacks.
	 * Do not remove any html tags if second argument $cleanTags is false.
	 *
	 * @access public
	 * @param mixed $data to clean
	 * @param bool $cleanTags remove html tags
	 * @return string
	 */
	public static function cleanXss($data, $cleanTags = false) {
		
		/*
		 * Call this function recursively if $data argument is array.
		 */
		if(is_array($data)) {

			$tmpArr = array();
			
			/* Clean keys also, if array is associative */
			if(\Lib\Arrays::isAssoc($data)) {
				foreach($data as $key => $value) {
					/* Clean key. */
					$key = self::cleanXss($key);
					/* Clean value */
					$tmpArr[$key] = self::cleanXss($value);
				} // end foreach
			} else {
				foreach($data as $value) {
					$tmpArr[] = self::cleanXss($value);
				} // end foreach
			} // end if array assoc
			return $tmpArr;
			
		} elseif (is_string($data)) {
			
			/* Remove invisible chars */
			$data = preg_replace(self::$charsToRemove, '', $data);

			/* Replace dangerous expressions */
			foreach(self::$expressionsToReplaced as $key => $value) {
				$data = str_replace($key, $value, $data);
			}
			
			/* Remove dangerous script, etc */
			foreach(self::$patternsToRemove as $key => $value) {
				$data = preg_replace($key, $value, $data);
			}
			
			if($cleanTags == true) {
				
				/* Strip any html tag */
				$data = self::stripTags($data);
				
			} else {
				
				/* Remove any attribute starting with "on*" or "xmlns" */
				$data = self::stripAttributes($data);
				
				/*
				   * Remove elements which in something like user comments.
					*/
				do {
					$oldData = $data;
					$data = preg_replace('#</*(applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base)[^>]*>#i', '', $data);
				} while ($oldData != $data);
				
				/*
				   * Clean source code, replace javascript and php
				   * vulnerable functions to special chars.
					*/
				if($cleanTags == true) {
					$data = self::cleanSource($data);
				}
				
				/* Strip any script definition */
				$data = self::stripScriptTags($data);
				
			} // end if clean tags
			
			return $data;

		} else {

		    // Nothing to change in string
		    return $data;

        }
		
	} // end func cleanXss
	
	/**
	 * Fix source code, replace javascript and php
	 * vulnerable functions to special chars.
	 *
	 * example: eval ("echo 333;"); -> eval &#40;"echo 333;"&#41;;
	 *
	 * @access public
	 * @param string $data to clean
	 * @return string
	 */
	public static function cleanSource($data) {
		return preg_replace('#(alert|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', "\\1\\2&#40;\\3&#41;", $data);
	} // end func cleanSource
	
	/**
	 * Return cleaned text with new lines for each application run mode.
	 *
	 * @access public
	 * @param  string
	 * @return string
	 */
	public static function cleanNL($data) {
		
		if(TK_RUN_MODE == 'cli') {
			return str_replace(
				array("\r\n", "\n\r", "\r", "<br>", "<br />", "<br/>", "<BR>", "<BR />", "<BR/>"),
				"\n",
				$data);
		} else {
			return nl2br($data);
		}
		
	} // end func cleanNL
	
	/**
	 * Clean any string to valid url
	 *
	 * @access public
	 * @param  string $data
	 * @param  mixed (array | null)
	 * @return string
	 */
	public static function cleanUrlString($data) {
		
		$allowedChars = array('.', '-', '_');
		$allowedLetters = range('a', 'z');
		$allowedNumbers = array('0','1','2','3','4','5','6','7','8','9');
		
		$allowedAll = array_merge($allowedChars, $allowedLetters, $allowedNumbers);
		
		$max = 254;
		$new = '';
		
		$data = str_replace(array("\r\n", "\r", "\n"), '', $data);
		$data = strtolower($data);
		$data = trim($data);
		
		$len = strlen($data);
		
		for($i = 0; $i < $len; $i++) {
			
			$l = substr($data, $i, 1);
			
			if($l == ' ') {
				$l = '-';
			}
			
			if(in_array($l, $allowedAll, true)) {
				$new .= $l;
			}
			
		} // End for
		
		$new = str_replace('---', '-', $new);
		$new = str_replace('--', '-', $new);
		
		$new = substr($new, 0, $max);
		
		$new = trim($new);
		$new = trim($new, '-');
		
		return $new;
		
	} // End func cleanUrlString
	
	/**
	 * Clean string as a-z, A-Z, 0-9.
	 * Allow chars defined in $allowedChars array.
	 *
	 * @access public
	 * @param mixed $data. Data to clean
	 * @param mixed $allowedChars. Allowed chars as an array
	 * @return string
	 */
	public static function stripChars($data, $allowedChars = NULL) {
		
		/* Make allowed chars pattern */
		
		$charsStr = '';
		
		if(is_array($allowedChars)) {
			foreach($allowedChars as $char) {
				$charsStr .= $char;
			}
		}
		
		return preg_replace("#[^a-z0-9A-Z".$charsStr."]#", '', $data);
		
	} // end func stripChars
	
	/**
	 * Remove any attribute starting with "on*" or "xmlns"
	 *
	 * @access public
	 * @param string $data to clean
	 * @return string
	 */
	public static function stripAttributes($data) {
		return preg_replace('#(?:on[a-z]+|xmlns)\s*=\s*["\x00-\x20]?[^>"]*[\'"\x00-\x20]?\s?#iu', '', $data);
	} // end func stripAttributes
	
	/**
	 * Convert tabs to char specified.
	 * By default will convert to empty string.
	 *
	 * @access public
	 * @param string $data
	 * @param string $char
	 * @return string
	 */
	public static function stripTabs($data, $char = '') {
		return str_replace("\t", $char, $data);
	} // end func stripTabs
	
	/**
	 * Strip image tags.
	 * Allow image source if $keepSrcAttr is true.
	 *
	 * @access public
	 * @param string $data to strip
     * @param bool $keepSrcAttr. Keep "src" attribute
	 * @return string
	 */
	public static function stripImageTags($data, $keepSrcAttr = false) {
		if($keepSrcAttr) {
			$srcStr = '$1';
		} else {
			$srcStr = '';
		}
		
		return preg_replace('#<img\s.*?(?:src\s*=\s*["\']?([^"\'<>\s]*)["\']?[^>]*)?>#is', $srcStr, $data);
		
	} // end func stripImageTags
	
	/**
	 * Strip hyperlinks.
	 *
	 * @access public
	 * @param string $data to strip
	 * @return string
	 */
	public static function stripHyperLinks($data) {
		return preg_replace('@<a[^>]*?>.*?</a>@si', '', $data);
	} // end func stripHyperLinks
	
	/**
	 * Strip meta tags.
	 *
	 * @access public
	 * @param string $data to strip
	 * @return string
	 */
	public static function stripMetaTags($data) {
		return preg_replace('#<meta\s.*?(?:content\s*=\s*["\']?([^"\'<>\s]*)["\']?[^>]*)?>#is', '', $data);
	} // end func stripMetaTags
	
	/**
	 * Strip style definitions.
	 *
	 * <style>...</style>
	 * <link href=".." rel="stylesheet" type="text/css">
	 *
	 * @access public
	 * @param string $data to strip
	 * @return string
	 */
	public static function stripStylesheetTags($data) {
		return preg_replace('/(<link[^>]+rel="[^"]*stylesheet"[^>]*>)|<style[^>]*>.*?<\/style>/is', '', $data);
	} // end func stripStylesheetTags
	
	/**
	 * Strip any script definition.
	 *
	 * @access public
	 * @param string $data to strip
	 * @return string
	 */
	public static function stripScriptTags($data) {
		
		$data = preg_replace('@<script[^>]*?>.*?</script>@si', '', $data);
		$data = preg_replace('@<script>@si', '', $data);
		
		$data = preg_replace('@<\?php[^>]*.*?\?>@si', '', $data);
		$data = preg_replace('@<\?[^>]*.*?\?>@si', '', $data);
		
		return $data;
		
	} // end func stripScriptTags
	
	/**
	 * Strip comments.
	 * Delete non html comments also if second argument is true.
	 *
	 * <!-- ... -->
	 * /*  * /
	 *
	 * @access public
	 * @param string $data to strip
     * @param bool $noneHtml. Strip none html comment too
	 * @return string
	 */
	public static function stripComments($data, $noneHtml = true) {
		
		// Remove html <!-- ... --> comments
		$data = preg_replace('@<![\s\S]*?--[ \t\n\r]*>@', '', $data);
		
		// Remove /* ... */
		if($noneHtml) {
			$data = preg_replace('%/\*[\s\S]+?\*/|(?://).*(?:\r\n|\n)%m', '', $data);
		}
		
		return $data;
		
	} // end func stripComments
	
	/**
	 * Strip any html tag.
	 *
	 * @access public
	 * @param string $data to strip
	 * @return string
	 */
	public static function stripTags($data) {
		return preg_replace('@<[\/\!]*?[^<>]*?>@si', '', $data);
	} // end func stripTags
	
	/**
	 * Strips extra whitespaces.
	 *
	 * @access public
	 * @param string $data
	 * @return string
	 */
	public static function stripWhitespaces($data) {
		
		$data = preg_replace('/[\n\r\t]+/', '', $data);
		return preg_replace('/\s{2,}/', ' ', $data);
		
	} // end func stripWhitespaces
	
	/**
	 * Encode html entity by application encoding.
	 *
	 * @access public
	 * @param mixed array | string data to encode
     * @param mixed $encoding
	 * @return string
	 */
	public static function encodeHtmlEntities($data, $encoding = NULL) {
		
		if(is_null($encoding)) {
			$encoding = 'UTF-8';
		}
		
		if(is_array($data)) {
			foreach($data as $key => $value) {
				$data[$key] = self::encodeHtmlEntities($value, $encoding);
			}
			
			return $data;
			
		} else {
			return htmlentities((string)$data, ENT_QUOTES, $encoding);
		}
		
	} // end func encodeHtmlEntities
	
	/**
	 * Decode html entity by application encoding.
	 *
	 * @access public
	 * @param mixed array | string data to decode
     * @param mixed $encoding
	 * @return string
	 */
	public static function decodeHtmlEntities($data, $encoding = NULL) {
		
		if(is_null($encoding)) {
			$encoding = 'UTF-8';
		}
		
		if(is_array($data)) {
			foreach($data as $key => $value) {
				$data[$key] = self::decodeHtmlEntities($value, $encoding);
			}
			
			return $data;
			
		} else {
			return html_entity_decode(urldecode($data), ENT_QUOTES, $encoding);
		}
		
	} // end func decodeHtmlEntities
	
} /* End of class Filter */