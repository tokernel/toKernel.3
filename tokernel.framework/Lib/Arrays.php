<?php
/**
 * toKernel by David A. and contributors.
 * Array processing class library
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
 * Arrays class
 *
 * @author David A. <tokernel@gmail.com>
 */
class Arrays {

    /**
     * Return true if the given array is associative.
     *
     * @access public
     * @param array $arr
     * @return bool
     */
    public static function isAssoc(Array $arr) {
        return array_keys($arr) !== range(0, count($arr) - 1);
    } // end func isAssoc

    /**
     * Rename key in array
     *
     * @access public
     * @param string $curKey
     * @param string $newKey
     * @param array $arr
     * @return array | bool
     */
    public static function keyRename($curKey, $newKey, Array $arr) {

        if(!is_array($arr)) {
            return false;
        }

        $newArr = array();

        foreach($arr as $key => $value) {
            if($key == $curKey and is_string($key)) {
                $key = $newKey;
            }

            $newArr[$key] = $value;
        } // end foreach

        return $newArr;

    } // end func keyRename

    /**
     * Return The given key's position in array.
     *
     * @access public
     * @param mixed $key
     * @param array $arr
     * @return int | bool
     */
    public static function keyPosition($key, Array $arr) {

        if(!is_array($arr) or is_null($key)) {
            return false;
        }

        $tmp = array_keys($arr);
        $index = array_search($key, $tmp);

        if($index !== false) {
            return $index + 1;
        } else {
            return false;
        }

    } // end func keyPosition

    /**
     * Return array key by given position
     *
     * @access public
     * @param  array $arr
     * @param  int $pos
     * @return mixed
     */
    public static function keyByPosition($pos, Array $arr) {

        $tmp = array_keys($arr);

        if(isset($tmp[$pos])) {
            return $tmp[$pos];
        }

        return false;

    } // end func keyByPosition

    /**
     * Check if all values of array elements is empty
     *
     * @access public
     * @param array $arr
     * @return bool
     */
    public static function isElementsValuesEmpty(Array $arr) {

        if(empty($arr)) {
            return true;
        }

        $tmp = '';

        foreach($arr as $key => $value) {
            if(!is_array($value)) {
                $tmp .= $value;
            } else {
                if(!empty($value)) {
                    return false;
                }
            }
        }

        if($tmp == '') {
            return true;
        } else {
            return false;
        }

    } // End func isElementsValuesEmpty

} /* End of class Arrays */