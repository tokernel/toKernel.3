<?php
/**
 * toKernel by David A. and contributors.
 * Language localisation library
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
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2017 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 3.0.0
 */

namespace Lib;

/* Restrict direct access to this file */
use Kernel\Base\ErrorExceptionHandler;

defined('TK_EXEC') or die('Restricted area.');

/**
 * Language class
 *
 * @author David A. <tokernel@gmail.com>
 */
class Language {
	
	/**
	 * Language file path
	 *
	 * @access protected
	 * @var string
	 */
	protected $languageFilePath;
	
	/**
	 * Loaded language object
	 * The object will not be loaded until first item request.
	 *
	 * @access protected
	 * @var object
	 */
	protected $language;
		
	/**
	 * Class constructor
	 *
	 * @access public
	 */
	public function __construct($languageFilePath) {

        $this->languageFilePath = $languageFilePath;
    	$this->language = NULL;
				
	} // end constructor

	/**
	 * Get language value by item
	 * Using second argument to pass values into item.
	 *
     * @throws \ErrorException
     * @throws \Exception
	 * @access public
	 * @param string $item
	 * @param array $lngArgs
	 * @return mixed string | bool
	 */
	public function get($item, array $lngArgs = array()) {
		
		if(trim($item) == '') {
		    throw new ErrorExceptionHandler('Translation expression is empty for language library! (Language file: `'.$this->languageFilePath.'`)');
		}
		
		$returnVal = '';
		
		// load language file to object if not loaded
		if(!is_object($this->language)) {
			$this->loadLanguageFile();
		}
	
		// Define item value if exists
		if($this->language->itemExists($item)) {
			$returnVal = $this->language->getItem($item);
		} else {
			throw new \ErrorException('Item `'.$item.'` not exists in Language file `'.$this->languageFilePath .'`!');
		}
		
		// Trigger error even if item exists but value is empty
		if(trim($returnVal) == '') {
			throw new \ErrorException('Item `'.$item.'` exists but value is empty in Language file: `'.$this->languageFilePath .'` !');
		}
		
		// Check if count of replacements greater than 0.
		$repsCountInStr = mb_substr_count($returnVal, '%s');
		$repsCountInArr = count($lngArgs);
		
		if($repsCountInStr > $repsCountInArr) {
			
			$errString = htmlspecialchars($item . '=' . $returnVal);

            throw new \Exception(
                'Too few arguments for translation expression `' . $errString.'` ' .
                'in language file ('.$this->languageFilePath.').'
            );

		}
		
		// Parse language expression arguments if not empty
		if(!empty($lngArgs)) {
			return vsprintf($returnVal, $lngArgs);
		} else {
			return $returnVal;
		}
		
	} // end func get
	
	/**
	 * Load language file.
	 *
	 * @access protected
	 * @return void
	 */
	protected function loadLanguageFile() {
		
		// Load language object
		$this->language = new \Lib\Ini($this->languageFilePath, NULL, false);

	} // end func loadLanguageFile
	
} /* End of class Language */