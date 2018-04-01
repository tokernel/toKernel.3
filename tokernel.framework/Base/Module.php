<?php
/**
 * toKernel by David A. and contributors.
 * Module base class
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
 * Module class
 *
 * @author David A. <tokernel@gmail.com>
 */
class Module {

    /**
     * Parent Addon name.
     *
     * @access private
     * @var string
     */
	private $addonName;

    /**
     * Current Module name.
     *
     * @access private
     * @var string
     */
	private $moduleName;

    /**
     * Module constructor.
     */
	public function __construct() {

	    // Define actual Module name.
		$this->moduleName = get_class($this);

        // Define parent Addon name.
		$this->addonName = explode('\\', $this->moduleName);
		$this->addonName = $this->addonName[1];

	} // End constructor

    /**
     * Load View file by path and return View Object.
     *
     * @access protected
     * @param string $path
     * @param array $vars
     * @return View
     */
    protected function loadView($path, $vars = array()) {

	    // Prepare Module path
		$moduleName = get_class($this);
		$modulePath = TK_APP_PATH . $moduleName . TK_DS;

        // Prepare View file path
		$viewPath = $modulePath . 'Views' . TK_DS . $path .'.php';
		$viewPath = str_replace(array('\\', '/'), TK_DS, $viewPath);

        // Define and return new View object.
		$view = new \Base\View($viewPath, $vars);

		return $view;

	} // End func loadView

    /**
     * Load and return Module object.
     *
     * @access protected
     * @param string $path
     * @param array $params
     * @return object
     */
	protected function loadModule($path, $params = array()) {
		
		// Prepare the path
		$moduleName = get_class($this);
		$addonName = explode('\\', $moduleName);
		$addonName = $addonName[1]; // Example
		$moduleCall = "\\Addons\\$addonName\\Modules\\$path";

        // Define and return new Module object.
		return new $moduleCall($params);

	} // End func loadModule

    /**
     * Load and return Model object
     *
     * @access protected
     * @param string $path
     * @param array $params
     * @return object
     */
    protected function loadModel($path, $params = array()) {

        // Prepare the path
        $moduleName = get_class($this);
        $addonName = explode('\\', $moduleName);
        $addonName = $addonName[1]; // Example
        $modelCall = "\\Addons\\$addonName\\Models\\$path";

        // Define and return new Model object.
        return new $modelCall($params);

    } // End func loadModel

    /**
     * Return Parent Addon object
     *
     * @access protected
     * @return object
     */
	protected function addonInstance() {

	    // Prepare the name
		$addonCall = "\\Addons\\$this->addonName";

        // Return Parent Addon object instance.
		return $addonCall::instance();

	} // End func addonInstance

    /**
     * Get Parent Addon's configuration value
     *
     * @access public
     * @param string $item
     * @param mixed string | null $section
     * @return mixed
     */
    public function getConfig($item, $section = NULL) {
        return $this->addonInstance()->getConfig($item, $section);
    }

    // @todo Finish this functionality
    public static function getLanguage($item) {
        return 'Hello!';
    }

} // End class Module

