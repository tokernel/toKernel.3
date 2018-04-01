<?php
/**
 * toKernel by David A. and contributors.
 * Addon base class
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
 * Addon class
 *
 * @author David A. <tokernel@gmail.com>
 */
abstract class Addon {

    /**
     * Loaded Addons collection
     *
     * @var array
     * @access private
     */
	private static $instances;

    /**
     * Addon configuration object
     *
     * @var object
     * @access private
     */
    private $config;

    /**
     * Addon constructor.
     *
     * @access protected
     */
	protected abstract function __construct();

    /**
     * Disable Cloning of object
     *
     * @access protected
     * @throws \Exception
     * @return void
     */
	protected final function __clone() {
		throw new Exception("Cannot unserialize singleton");
	}

    /**
     * Disable Waking up of object
     *
     * @access protected
     * @throws \Exception
     * @return void
     */
    protected final function __wakeup() {
		throw new Exception("Cannot unserialize singleton");
	}

    /**
     * Return the instance of Addon object.
     *
     * @access public
     * @return object
     */
	public static final function instance() {

	    $cls = get_called_class();
				
		if (!isset(self::$instances[$cls])) {
			self::$instances[$cls] = new $cls;
		}

		return self::$instances[$cls];

	} // End func instance

    /**
     * Load View file by path and return View Object.
     *
     * @access protected
     * @param string $path
     * @param array $vars
     * @return View
     */
	protected function loadView($path, $vars = array()) {

	    // Prepare the path
        $addonName = get_class($this);
        $addonPath = TK_APP_PATH . $addonName . TK_DS;
        $viewPath = $addonPath . 'Views' . TK_DS . $path .'.php';
        $viewPath = str_replace(array('\\', '/'), TK_DS, $viewPath);

        // Define and Return a new object of View
		$view = new \Base\View($viewPath, $vars);

		return $view;

	} // End func loadView

    /**
     * Load and return Module object.
     *
     * @access public
     * @param string $path
     * @param array $params
     * @return object
     */
	public function loadModule($path, $params = array()) {

	    // Prepare the path
	    $addonName = get_class($this);
		$moduleCall = "\\$addonName\\Modules\\$path";

        // Load and return new Module object
        return new $moduleCall($params);

	} // End func loadModule

    /**
     * Load and return Model object
     *
     * @access public
     * @param string $path
     * @param array $params
     * @return object
     */
	public function loadModel($path, $params = array()) {

	    // Prepare the path
	    $addonName = get_class($this);
		$modelCall = "\\$addonName\\Models\\$path";

        // Load and return new Model object
        return new $modelCall($params);

	} // End func loadModel

    /**
     * Get Addon's configuration value
     *
     * @access public
     * @param string $item
     * @param mixed string | null $section
     * @return mixed
     */
	public function getConfig($item, $section = NULL) {

	    // Configuration object still not loaded
	    if(is_null($this->config)) {

	        // Prepare the configuration file path
	        $addonName = get_class($this);
            $addonPath = TK_APP_PATH . $addonName . TK_DS;
            $configFilePath = $addonPath . 'Config' . TK_DS . 'config.ini';
            $configFilePath = str_replace(array('\\', '/'), TK_DS, $configFilePath);

            // Define the Configuration object of Addon.
            $this->config = new \Lib\Ini($configFilePath);
        }

        // Return Configuration value
        return $this->config->getItem($item, $section);

	} // End func getConfig

    // @todo Finish this functionality
	public static function getLanguage($item) {
		return 'Hello!';
	}

} // End of class Addon

