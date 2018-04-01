<?php
/**
 * toKernel by David A. and contributors.
 * View base class
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
 * @subpackage base
 * @author     David A. <framework@tokernel.com>
 * @copyright  Copyright (c) 2017 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 3.0.0
 */

namespace Base;

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * View class
 *
 * @author David A. <tokernel@gmail.com>
 */
class View {

    /**
     * View File variables
     *
     * @access private
     * @var array
     */
	private $vars = array();

    /**
     * View file path
     *
     * @access private
     * @var string
     */
	private $path;

    /**
     * View constructor.
     *
     * @access public
     * @param string $viewPath
     * @param array $vars
     */
	public function __construct($viewPath, $vars = array()) {
		$this->path = $viewPath;
		$this->vars = $vars;
	}

    /**
     * Return Parsed view file content as a string.
     *
     * @access public
     * @param array $vars
     * @return string
     */
	public function getParsed($vars = array()) {

	    // Merge new Variables with existing.
		if(!empty($vars)) {
			$this->vars = array_merge($this->vars, $vars);
		}

		// Loading the view file to get content as a string.
		ob_start();
		require_once $this->path;
		
		$content = ob_get_contents();
				
		ob_end_clean();
		
		// Replace all variables
		if(!empty($this->vars)) {
			foreach($this->vars as $var => $value) {
				// Convert only scalar.
				if(is_scalar($value)) {
					$content = str_replace('{var.'.$var.'}', $value, $content);
				}
			}
		}
				
		return $content;

	} // End func getParsed

    /**
     * Display Parsed view file content.
     *
     * @access public
     * @param array $vars
     * @return void
     */
	public function displayParsed($vars = array()) {
		echo $this->getParsed($vars);
	}
	
} // End of class View
