<?php
namespace CLI;

class Runner extends \Kernel\Base\Runner {

    /**
     * Private constructor to prevent it being created directly
     *
     * @final
     * @access private
     */
    final private function __construct() {

    }

    public static final function run($argv) {

        ob_start();

        try {

            // Initialize the application configuration
            self::$config = new \Lib\Ini(TK_APP_PATH . 'Config' . TK_DS . 'application.ini');

            // Initialize the response
            Response::init();

            // Initialize the Request and get the HTTP Interface configuration in any.
            Request::init($argv, self::$config);

            // Start to define Addon and action to Call by Request.
            $addonName = Request::addon();

            // Check if addon exists
            if(!self::addonExists($addonName)) {
                \CLI\Response::outputUsage('Addon ' . $addonName . ' not exists!');
                exit(1);
            }

            // Ucfirst the Addon class name.
            $addonName = ucfirst($addonName);

            // Define Addon Callable name.
            $addonNameCallString = "\\Addons\\$addonName";

            // Define Addon object
            $addon = $addonNameCallString::instance();

            // Define Action name with prefix for CLI call.
            $action = 'cli_' . Request::action();

            // Check of Addon's action exists
            if(!method_exists($addon, $action)) {
                \CLI\Response::outputUsage('Action ' . $action . '() of Addon ' . $addonName . ' not exists!');
                exit(1);
            }

            $addon->$action();

        } catch (\CLI\DisplayErrorException $e) {
            \CLI\ErrorExceptionHandler::DisplayError($e);
        } catch (\ErrorException $e) {
            \CLI\ErrorExceptionHandler::DisplayError($e);
        } catch(\Exception $e) {
            \CLI\ErrorExceptionHandler::DisplayError($e);
        }

    }



}