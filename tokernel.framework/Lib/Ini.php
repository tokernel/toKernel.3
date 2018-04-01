<?php
/**
 * toKernel by David A. and contributors.
 * ini file processing library
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
 */

namespace Lib;

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * ini_lib class
 *
 * @author David A. <tokernel@gmail.com>
 */
class Ini {

    /**
     * Main ini array, content of ini file.
     *
     * @access protected
     * @var array
     */
    protected $iniArr;

    /**
     * Loaded INI file name
     *
     * @access protected
     * @var string
     */
    protected $IniFilePath;

    /**
     * String -end of file.
     * i.e. "; End of file. Last update 2017-08-01"
     *
     * @access protected
     * @var string
     */
    protected $endOfFile;

    /**
     * Is file opened as new (empty).
     *
     * @access protected
     * @var bool
     */
    protected $isNewFile;

    /**
     * Comment start char.
     *
     * @access protected
     * @var string
     */
    protected $commentStartChar = ';';

    /**
     * Is file opened as readonly
     * This value will be true, if loading
     * ini file with specified section.
     *
     * @access protected
     * @var bool
     */
    protected $isFileReadOnly;

    /**
     * Is loaded file have sections
     *
     * @access protected
     * @var bool
     */
    protected $hasSections;

    /**
     * Class constructor
     *
     * @access public
     * @param string $filePath
     * @param string $sectionName
     * @param bool $createIfNotExists
     */
    public function __construct($filePath, $sectionName = NULL, $createIfNotExists = false) {

        $this->loadFile($filePath, $sectionName, $createIfNotExists);

    } // end constructor

    /**
     * Class destructor
     *
     * @access public
     * @return void
     */
    public function __destruct() {
        unset($this->iniArr);
        unset($this->IniFilePath);
        unset($this->isNewFile);
        unset($this->endOfFile);
        unset($this->commentStartChar);
        unset($this->isFileReadOnly);
        unset($this->hasSections);
    } // end destructor

    /**
     * Magic function. Return item from ini.
     *
     * @access public
     * @param string $item
     * @return mixed
     */
    public function __get($item) {
        return $this->getItem($item);
    }

    /**
     * Magic function set. set ini item value.
     *
     * @access public
     * @param string $item
     * @param mixed $value
     * @return bool
     */
    public function __set($item, $value) {
        return $this->setItem($item, $value);
    }

    /**
     * Magic function isset. return, is item exists.
     *
     * @access public
     * @param string $item
     * @return mixed
     */
    public function __isset($item) {
        return $this->itemExists($item);
    }

    /**
     * Magic function unset. delete ini item.
     *
     * @access public
     * @param string $item
     * @return mixed
     */
    public function __unset($item) {
        $this->deleteItem($item);
    }

    /**
     * Load ini file
     *
     * @access public
     * @throws \ErrorException
     * @param  string $file
     * @param  string $section
     * @param  bool $createIfNotExists
     * @return bool
     */
    public function loadFile($file, $section = NULL, $createIfNotExists = false) {

        // If file doesn't exists and trying to load as not new
    	if(!is_readable($file) and $createIfNotExists == false) {
    	    throw new \ErrorException('File `'.$file.'` not readable!');
        }
	    
        // If file doesn't exist then create new instance
        if(!is_readable($file)) {
            
        	$this->IniFilePath = $file;
            $this->isNewFile = true;

            if(!is_null($section)) {
                $this->setSection($section);
            }
		        
            return true;
        }
        
        $lines = file($file, FILE_IGNORE_NEW_LINES);

        $this->IniFilePath = $file;

        $currentSection = NULL;

        foreach($lines as $line) {

            $line = trim($line);

            // Define section
	        if($this->isSection($line)) {
            
                // Get section name
                $tmpSection = trim(substr($line, 1, -1));

                if($tmpSection != '') {

                    $currentSection = $tmpSection;

                    $this->setSection($currentSection);
                    $this->hasSections = true;
                } else {
                    $this->setComment('; INVALID LINE: ['.$tmpSection.']');
                }

                // define comment
            } elseif($this->isComment($line) == true) {

                if($this->isEndOfFile($line) == false) {
                    $this->setComment($line, $currentSection);
                }

                // define params
            } else {
                if($line != '') {
                    $equalSignPos = strpos($line, '=');

                    if($equalSignPos === false) {
                        $line = '; INVALID LINE: ' . $line;

                        $this->setComment($line, $currentSection);
                    } else {
                        $key = trim(substr($line, 0, $equalSignPos));
                        $value = trim(substr($line, $equalSignPos+1));

                        $this->setItem($key, $value, $currentSection);
                    }
                }
            }

        } // End foreach lines

        unset($lines);

        if(is_null($section)) {
            return true;
        }

        if(!$this->sectionExists($section)) {
            throw new \ErrorException("Section `".$section."` doesn't exists to load.");
        }

        $tmpArr = $this->iniArr[$section];
        $this->iniArr = array();
        $this->iniArr[$section] = $tmpArr;
        $this->isFileReadOnly = true;

        return true;

    } // end func loadFile

    /**
     * Save ini file
     *
     * @access public
     * @throws \ErrorException
     * @param string $file
     * @param bool $overwrite
     * @return bool
     */
    public function saveFile($file = NULL, $overwrite = false) {

        if($this->isFileReadOnly == true and $overwrite == false and is_null($file)) {
            return false;
        }

        if(is_file($file) and $overwrite == false) {
            return false;
        }

        if(is_null($file)) {
            $file = $this->IniFilePath;
        }

        if($file == '') {
            return false;
        }

        $iniBuffer = '';

        foreach($this->iniArr as $key => $value) {

            // Not an array, just core items without section
            // Set comments and item=value
            if(!is_array($value)) {

                $value = trim($value);

                // If comment, than add without key, Just line
                if(is_numeric($key) and $this->isComment($value)) {
                    $iniBuffer .= $value . "\n";
                    // Add as key=value
                } else {
                    $iniBuffer .= $key . '=' . $value . "\n";
                }

                // Array of items (section)
            } else {

                // define section name
                $iniBuffer .= '[' . $key . ']' . "\n";

                foreach($value as $itemKey => $itemValue) {

                    $itemValue = trim($itemValue);

                    // If comment, than add without key, Just line
                    if(is_numeric($itemKey) and $this->isComment($itemValue)) {
                        $iniBuffer .= $itemValue . "\n";
                        // Add as key=value
                    } else {
                        $iniBuffer .= $itemKey . '=' . $itemValue . "\n";
                    }

                } // End foreach section items

                // New line after section end
                $iniBuffer .= "\n";
            }

        } // end foreach

        $iniBuffer = trim($iniBuffer);
        $iniBuffer .= $this->endOfFile;

        if(!is_writable($file)) {
            throw new \ErrorException('File `'.$file.'` is not writable!');
        } else {
            return file_put_contents($file, $iniBuffer);
        }

    } // end func saveFile

    /**
     * Destroy object values.
     *
     * @access public
     * @return void
     */
    public function unloadFile() {
        $this->__destruct();
    } // end func unloadFile

    /**
     * Delete loaded ini file
     *
     * @access public
     * @param bool $unloadIniObject = false
     * @return void
     */
    public function deleteFile($unloadIniObject = false) {

        $tmpFilePath = $this->IniFilePath;

        if($unloadIniObject) {
            $this->__destruct();
        }

        if(is_writable($this->IniFilePath)) {
            unlink($tmpFilePath);
        }

    } // end func deleteFile

    /**
     * Return loaded ini file name
     *
     * @access public
     * @return string
     */
    public function getFilePath() {
        return $this->IniFilePath;
    }

    /**
     * Create section if not exists.
     * Items array is optional.
     *
     * @access public
     * @throws \InvalidArgumentException
     * @param string $section
     * @param array $items
     * @return bool
     */
    public function setSection($section, $items = array()) {

        $section = trim($section);
	    
        /* Check for valid section name */
        if(!$this->isValidName($section)) {
            throw new \InvalidArgumentException('Invalid section name `'.$section.'`!');
        }
        
        /* Create new section if not exists */
        if(!$this->sectionExists($section)) {
	        $this->iniArr[$section] = array();
        }

        if(count($items) > 0) {
            foreach($items as $item => $value) {
                if($this->isValidName($item)) {
                    $this->setItem($item, $value, $section);
                }
            }
        }

        return true;

    } // end function setSection

    /**
     * Return array of existing section names
     *
     * @access public
     * @return array
     */
    public function getSectionNames() {

        $sections = array();

        foreach($this->iniArr as $section => $items) {
            if(is_array($items)) {
                $sections[] = $section;
            }
        }
        return $sections;

    } // end function getSectionNames

    /**
     * Return section as array.
     *
     * @access public
     * @param string $section
     * @return mixed array | bool
     */
    public function getSection($section) {

        if(isset($this->iniArr[$section]) and is_array($this->iniArr[$section])) {
            return $this->cleanArray($this->iniArr[$section]);
        } else {
            return false;
        }

    } // end func getSection

    /**
     * Check is section exists
     *
     * @access public
     * @param string $section
     * @return bool
     */
    public function sectionExists($section) {
        if(isset($this->iniArr[$section]) and is_array($this->iniArr[$section])) {
            return true;
        } else {
            return false;
        }
    } // end func sectionExists

    /**
     * Rename section name
     *
     * @access public
     * @param string $sectionName
     * @param string $newSectionName
     * @return bool
     */
    public function renameSection($sectionName, $newSectionName) {

        $newSectionName = trim($newSectionName);
        $sectionName = trim($sectionName);

        if($this->sectionExists($sectionName) == false or
            $this->isValidName($sectionName) == false or
            $this->isValidName($newSectionName) == false) {
            return false;
        }

        $this->iniArr = \Lib\Arrays::keyRename($sectionName, $newSectionName, $this->iniArr);

        return true;

    } // end func renameSection

    /**
     * Delete section
     *
     * @access public
     * @param string $sectionName
     * @return bool
     */
    public function deleteSection($sectionName) {
        if($this->sectionExists($sectionName)) {
            unset($this->iniArr[$sectionName]);
            return true;
        } else {
            return false;
        }
    } // end func deleteSection

    /**
     * Sort section item.
     * For now, this function will remove all comments in sorted section.
     * If the argument $sectionOrEntireIniArr defined as true, then
     * function will sort entire ini file, else if section name specified,
     * function will sort specified section.
     *
     * @access public
     * @param mixed string | bool $sectionOrEntireIniArr
     * @return bool
     */
    public function sortSectionItems($sectionOrEntireIniArr) {

        // case, when section name set
        if(is_string($sectionOrEntireIniArr)) {
            if($this->sectionExists($sectionOrEntireIniArr) == true) {

                $this->iniArr[$sectionOrEntireIniArr] =
                    $this->cleanArray($this->iniArr[$sectionOrEntireIniArr]);

                ksort($this->iniArr[$sectionOrEntireIniArr]);
                return true;
            } else {
                return false;
            }
        } elseif($sectionOrEntireIniArr === true) {
            foreach($this->iniArr as $key => $value) {
                if(is_array($value) and !is_numeric($key)) {
                    $this->iniArr[$key] = $this->cleanArray($this->iniArr[$key]);
                    ksort($this->iniArr[$key]);
                }
            }
            return true;
        }

        return false;
        
    } // end func sortSectionItems

    /**
     * Set item value.
     * If the value defined as bool, then it will converted to 1 or 0.
     *
     * @access public
     * @param string $item
     * @param mixed $value
     * @param string $section
     * @return bool
     */
    public function setItem($item, $value, $section = NULL) {

        $value = trim($value);

        if(!$this->isValidName($item)) {
            return false;
        }

        if(is_bool($value) and $value === true) {
            $value = '1';
        } elseif(is_bool($value) and $value === false) {
            $value = '0';
        }

        if(!is_null($section)) {
            if(!$this->isValidName($section)) {
                return false;
            }

            $this->iniArr[$section][$item] = $value;
        } else {

            // Set this item on top, before any section start.
            if(!isset($this->iniArr[$item]) and $this->hasSections == true) {
                $this->iniArr = array($item => $value) + $this->iniArr;
            } else {
                $this->iniArr[$item] = $value;
            }
        }

        return true;

    } // end func setItem

    /**
     * Get all items before any section
     * The case when the ini file contains first items without section.
     * Collecting items before first section.
     *
     * @access public
     * @return mixed bool | array
     */
    public function getFirstItems() {

        $items = array();

        if(empty($this->iniArr)) {
            return $items;
        }

        foreach($this->iniArr as $item => $value) {

            // This is a section
            if(is_array($value)) {
                break;
            }

            if($this->isComment($value) or is_numeric($item)) {
                continue;
            }

            $items[$item] = $value;

        }

        return $items;

    } // End func getFirstItems

    /**
     * Return item value
     * if second argument defined as true, then this function
     * will return first finded item in entire ini array
     *
     * @access public
     * @param string $item
     * @param mixed string | bool $sectionNameOrEntireFile
     * @return mixed
     */
    public function getItem($item, $sectionNameOrEntireFile = NULL) {

        $item = trim($item);

        if($this->isValidName($item) == false) {
            return false;
        }

        // Find and return first item value of entire ini array.
        if($sectionNameOrEntireFile === true) {
            // search in main ini entire array

            if($this->itemExists($item) === true) {
                // exists in array root sections
                return $this->iniArr[$item];
            } else {
                // search in sections
                foreach($this->iniArr as $key => $value) {
                    if(is_array($value) and is_string($this->itemExists($item, $key))) {
                        return $value[$item];
                    }
                }
            }

            return false;
        }

        // Find and return by item name only
        if(is_null($sectionNameOrEntireFile) and $this->itemExists($item) === true) {
            return $this->iniArr[$item];
        }

        if($this->isValidName($sectionNameOrEntireFile)) {

            $in_section = $this->itemExists($item, $sectionNameOrEntireFile);

            if(is_string($in_section)) {
                return $this->iniArr[$sectionNameOrEntireFile][$item];
            }
        }

        return false;

    } // end func getItem

    /**
     * Check is item exists
     * if item found in section, then return section name, else
     * return true if item found behind a section.
     *
     * @access public
     * @param string $item
     * @param mixed $sectionName
     * @return mixed string | bool
     */
    public function itemExists($item, $sectionName = NULL) {

        if(!$this->isValidName($item)) {
            return false;
        }

        // Find in entire array
        if(is_null($sectionName) and isset($this->iniArr[$item])
            and !is_array($this->iniArr[$item])) {
            return true;
        }

        // check section
        if(!is_null($sectionName) and $this->isValidName($sectionName) == false) {
            return false;
        }

        if(isset($this->iniArr[$sectionName][$item])) {
            return $sectionName;
        }

        return false;

    } // end func itemExists

    /**
     * Rename item.
     *
     * @access public
     * @param string $itemName
     * @param string $newItemName
     * @param mixed $sectionName
     * @return mixed string | bool
     */
    public function renameItem($itemName, $newItemName, $sectionName = NULL) {

        // find replace in entire array
        if(is_null($sectionName) and $this->itemExists($itemName)) {
            $this->iniArr = \Lib\Arrays::keyRename($itemName, $newItemName, $this->iniArr);
            return true;
        }

        // check section
        if(!is_null($sectionName) and $this->isValidName($sectionName) == false) {
            return false;
        }

        // Search in section if isset
        if($this->itemExists($itemName, $sectionName)) {
            $this->iniArr[$sectionName] = \Lib\Arrays::keyRename($itemName, $newItemName, $this->iniArr[$sectionName]);
            return $sectionName;
        }

        return false;

    } // end func renameItem

    /**
     * Delete item
     *
     * @access public
     * @param string $itemName
     * @param mixed $sectionName
     * @return mixed string | bool
     */
    public function deleteItem($itemName, $sectionName = NULL) {

        // find replace in entire array
        if(is_null($sectionName) and $this->itemExists($itemName)) {
            unset($this->iniArr[$itemName]);
            return true;
        }

        // check section
        if(!is_null($sectionName) and $this->isValidName($sectionName) == false) {
            return false;
        }

        // Search in section if isset
        if($this->itemExists($itemName, $sectionName)) {
            unset($this->iniArr[$sectionName][$itemName]);
            return $sectionName;
        }

        return false;

    } // end func deleteItem

    /**
     * Add comment
     *
     * @access public
     * @param string $line
     * @param string $section
     * @return void
     */
    public function setComment($line, $section = NULL) {

        // Check, if line starting with comment sign ";"
        $line = trim($line);

        if(!$this->isComment($line)) {
            $line = $this->commentStartChar . " " . $line;
        }

        if(!is_null($section)) {
            $this->iniArr[$section][] = $line;
        } else {
            $this->iniArr[] = $line;
        }

    } // end func setComment

    /**
     * Check, is data comment.
     *
     * @access public
     * @param mixed $line
     * @return bool
     */
    public function isComment($line) {

        if(is_array($line)) {
            return false;
        }

        if(substr(trim($line), 0, 1) == $this->commentStartChar) {
            return true;
        } else {
            return false;
        }

    } // end func isComment

    /**
     * Check, is data section
     *
     * @access public
     * @param mixed $line
     * @return bool
     */
    public function isSection($line) {

        $line = trim($line);

        if(substr($line, 0, 1) == '[' and substr($line, -1) == ']') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check is valid section or item name.
     * Will be string.
     *
     * @access public
     * @param mixed $data
     * @return bool
     */
    public function isValidName($data) {

        if(trim($data) == '' or is_numeric($data) or
            $this->isComment($data) or is_bool($data)) {
            return false;
        } else {
            return true;
        }

    } // end func isValidName

    /**
     * Check, is string end of file.
     *
     * @access public
     * @param mixed $line
     * @return bool
     */
    public function isEndOfFile($line) {

        if(substr(trim($line), 0, 13) == $this->commentStartChar.' End of file') {
            return true;
        } else {
            return false;
        }

    } // end func isEndOfFile

    /**
     * Return true if current file is writable
     *
     * @access public
     * @return bool
     */
    public function isFileWritable() {

        if($this->IniFilePath == '') {
            return false;
        }

        if(!is_writable($this->IniFilePath)) {
            return false;
        }

        return true;

    } // End func isFileWritable

    /**
     * clean array, remove comments.
     *
     * @access protected
     * @param array $arr
     * @return mixed array | bool
     */
    protected function cleanArray($arr) {

        if(!is_array($arr)) {
            return false;
        }

        foreach($arr as $key => $value) {

            if(is_int($key)) {
                unset($arr[$key]);
            }

        } // end foreach

        return $arr;

    } // end func cleanArray

} /* End of class Ini */