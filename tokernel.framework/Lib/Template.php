<?php
/**
 * toKernel by David A. and contributors.
 * Template Processor class library.
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
defined('TK_EXEC') or die('Restricted area.');

/**
 * Template class
 *
 * @author David A. <tokernel@gmail.com>
 */
class Template {
	
	/**
	 * Template buffer to parse
	 *
	 * @var string
	 * @access protected
	 */
	protected $buffer;
	
	/**
	 * Template file
	 *
	 * @var string
	 * @access protected
	 */
	protected $templateFileName;
	
	/**
	 * Template parsing variables
	 *
	 * @var array
	 * @access protected
	 */
	protected $templateVars = array();
	
	/**
	 * Class constructor
	 *
	 * @access public
     * @param string $templateFileName
     * @param array $templateVars
	 */
	public function __construct($templateFileName, $templateVars = array()) {
		
		$this->templateFileName = $templateFileName;
        $this->templateVars = $templateVars;

        /* Get template buffer */
        ob_start();

        require($this->templateFileName);
        $this->buffer = ob_get_contents();

        ob_end_clean();

	} // end of func __construct

    /**
     * Interpret the template and return parsed content
     *
     * @access public
     * @param string $replaceThisContent replace "__THIS__" widget with this string
     * @return string
     */
    public function getParsed($replaceThisContent = NULL) {

        $parsedWidgetsCount = 0;

        /* Replace/Remove some symbols in template buffer */
        $buffer = $this->buffer;

        $buffer = str_replace("\n", '!_TK_NL_@', $buffer);
        $buffer = str_replace("\r", '', $buffer);

        $buffer = str_replace('<!--', '<TK_CMT', $buffer);
        $tmpArr = explode('<TK_CMT', $buffer);

        $templateBuffer = '';

        foreach($tmpArr as $part) {

            /*
             * If the line defined as widget, this engine will try to interpret it.
             * Example of widget definition tag in template file:
             * <!-- widget addon="Users" action="LoginForm" params="param1=param1_value|param2=param2_value" -->
             *
             * It is also possible to define Addon's module directly.
             * Example:
             * <!-- widget addon="Users" module="Auth" action="LoginForm" params="param1=param1_value|param2=param2_value" -->
             */

            $part = trim($part);

            if(strtolower(substr($part, 0, 6)) == 'widget') {
                $pos = strpos($part, '-->');
                $widgetPart = substr($part, 0, $pos);

                $tmpAddonDataArr = $this->parseWidgetTag($widgetPart);

                if(trim($tmpAddonDataArr['addon']) != '') {

                    if($tmpAddonDataArr['addon'] == '__THIS__') {

                        $widgetBuffer = $replaceThisContent;

                        $templateBuffer .= $widgetBuffer;
                        unset($replaceThisContent);
                        unset($widgetBuffer);

                    } else {

                        $templateBuffer .= $this->getWidgetParsedBuffer($tmpAddonDataArr);
                        $parsedWidgetsCount++;

                    }
                }

                unset($tmpAddonDataArr);

                $templateBuffer .= substr($part, $pos+3, strlen($part));

            } else {

                if($templateBuffer != '') {
                    $templateBuffer .= '<!--' . $part;
                } else {
                    $templateBuffer .= $part;
                }
            }

        } // end foreach

        $templateBuffer = str_replace("!_TK_NL_@", "\n", $templateBuffer);
        $templateBuffer = str_replace("\n\n", "\n", $templateBuffer);

        /* Replace all variables */
        if(!empty($this->templateVars)) {
            foreach($this->templateVars as $var => $value) {
                $templateBuffer = str_replace('{var.'.$var.'}', $value, $templateBuffer);
            }
        }

        $this->buffer = $templateBuffer;

        return $this->buffer;

    } // end func getParsed

	/**
	 * Parse widget definition tag and return array.
	 * Example of widget definition tag:
	 * <!-- widget addon="Users" action="LoginForm"
	 *      params="param1=param1_value|param2=param2_value" -->
	 *
     * Example with Module:
     * <!-- widget addon="Users" module="Auth" action="LoginForm"
     *      params="param1=param1_value|param2=param2_value" -->
     *
	 * @access protected
	 * @param string $str (widget definition tag)
	 * @return mixed array | false
	 */
	protected function parseWidgetTag($str) {
		
		$widgetItemsArr = array();
		
		/* get addon ID */
		$pos = strpos($str, 'addon="');
		if($pos === false) {
			return false;
		}
		
		$tmp = substr($str, ($pos + strlen('addon="')), strlen($str));
		$tmp = substr($tmp, 0, strpos($tmp, '"'));
		
		if(trim($tmp) == '') {
			return false;
		} else {
			$widgetItemsArr['addon'] = $tmp;
		}
		
		/* get addon module */
		$pos = strpos($str, 'module="');
		if($pos === false) {
			$widgetItemsArr['module'] = '';
		} else {
			$tmp = substr($str, ($pos + strlen('module="')), strlen($str));
			$tmp = substr($tmp, 0, strpos($tmp, '"'));
			
			if(trim($tmp) == '') {
				$widgetItemsArr['module'] = '';
			} else {
				$widgetItemsArr['module'] = $tmp;
			}
		} // end if pos.
		
		/* get addon action */
		$pos = strpos($str, 'action="');
		if($pos === false) {
			$widgetItemsArr['action'] = '';
		} else {
			$tmp = substr($str, ($pos + strlen('action="')), strlen($str));
			$tmp = substr($tmp, 0, strpos($tmp, '"'));
			
			if(trim($tmp) == '') {
				$widgetItemsArr['action'] = '';
			} else {
				$widgetItemsArr['action'] = $tmp;
			}
		} // end if pos.
		
		/* get addon params */
		$pos = strpos($str, 'params="');
		if($pos === false) {
			$widgetItemsArr['params'] = array();
		} else {

		    $tmp = substr($str, ($pos + strlen('params="')), strlen($str));
			$tmp = substr($tmp, 0, strpos($tmp, '"'));
			
			if(trim($tmp) == '') {
				$widgetItemsArr['params'] = array();
			} else {
				$tmp = explode('|', $tmp);
				
				foreach($tmp as $param) {
					$ptmp = explode('=', $param);
					if(trim($ptmp[0]) != '') {
						if(isset($ptmp[1])) {
							$widgetItemsArr['params'][$ptmp[0]] = $ptmp[1];
						} else {
							$widgetItemsArr['params'][$ptmp[0]] = NULL;
						}
					}
				} // end foreach
				
			}
		} // end if pos.
		
		return $widgetItemsArr;
		
	} // end func parseWidgetTag
	
	/**
	 * Run Addon's method or Addon's Module's method by widget definition and return buffer of result.
	 *
	 * @access protected
	 * @param array $tmpAddonDataArr
	 * @return string
	 */
	protected function getWidgetParsedBuffer($tmpAddonDataArr) {
		
		$widgetBuffer = '';
		
		/* Call addon action */
		$addonCallableName = '\Addons\\'.$tmpAddonDataArr['addon'];
        $addon = $addonCallableName::instance();

		ob_start();
			
        // Option 1. Defined addon module to load
        if($tmpAddonDataArr['module'] != '') {

            $module = $addon->loadModule($tmpAddonDataArr['module'], $tmpAddonDataArr['params']);
            $module->$tmpAddonDataArr['action']($tmpAddonDataArr['params']);

        // Option 2. Defined only addon action to call
        } else {
            $addon->$tmpAddonDataArr['action']($tmpAddonDataArr['params']);
        }

        $widgetBuffer .= ob_get_contents();
        ob_end_clean();

		return $widgetBuffer;
		
	} // end func getWidgetParsedBuffer
	
} /* End of class Template */
