<?php
namespace HTTP;

class Runner extends \Kernel\Base\Runner {

    /**
     * Private constructor to prevent it being created directly
     *
     * @final
     * @access private
     */
    final private function __construct() {

    }

    public static final function run() {

        ob_start();

        try {

            // Initialize the application configuration
            self::$config = new \Lib\Ini(TK_APP_PATH . 'Config' . TK_DS . 'application.ini');

            // Initialize the Request and get the HTTP Interface configuration in any.
            self::$http_interface = Request::init(self::$config);

            // Check if interface is under maintenance.
            if(self::getInterfaceConfig('under_maintenance')) {
                self::underMaintenance();
            }

            // Start to define Addon and action to Call by Request.
            $addonName = Request::addon();

            // Check if addon exists
            if(!self::addonExists($addonName)) {
                self::error404('Addon ' . $addonName . ' not exists!', __FILE__, __LINE__);
            }

            // Ucfirst the Addon class name.
            $addonName = ucfirst($addonName);

            // Define Addon Callable name.
            $addonNameCallString = "\\Addons\\$addonName";

            // Define Addon object
            $addon = $addonNameCallString::instance();

            // Define Action name
            $action = Request::action();

            // If it is ajax request, the action (method) name contains other prefix.
            if(Request::isAjax()) {
                $action = 'http_ajax_' . $action;
            } else {
                $action = 'http_' . $action;
            }

            // Check of Addon's action exists
            if(!method_exists($addon, $action)) {
                self::error404('Action ' . $action . '() of Addon ' . $addonName . ' not exists!', __FILE__, __LINE__);
            }


            // @todo Caching of HTTP interface!
            // cache_config
            // if request method GET !!!
            // work with cache if cached...
            $cacheId = md5($addonName.$action);

            // @todo finalize the output process. with addon functionality.
            ob_start();

            // Set Response Content type defined by default in interface configuration.
            // This can be changed in Addon action.
            $contentType = self::getInterfaceConfig('content_type');

            \HTTP\Response::setContentType($contentType);

            // Call Addon action.
            $addon->$action();

            $content = ob_get_clean();

            if(!\HTTP\Response::outputSent()) {

                if($contentType == 'application/json') {
                    \HTTP\Response::setJsonContent($content);
                } else {
                    \HTTP\Response::setContent($content);
                }

                \HTTP\Response::output();
            } else {
                echo $content;
            }


        } catch (\HTTP\DisplayErrorException $e) {
            \HTTP\ErrorExceptionHandler::DisplayError($e);
        } catch (\ErrorException $e) {
            \HTTP\ErrorExceptionHandler::DisplayError($e);
        } catch(\Exception $e) {
            \HTTP\ErrorExceptionHandler::DisplayError($e);
        }

    }

    public static function getInterfaceConfig($item) {

        if(isset(self::$http_interface[$item])) {
           return self::$http_interface[$item];
        }

        return false;

    }

    // @todo ! this and others should get errors from language!
    public static function error404($message = NULL, $file = NULL, $line = NULL) {
        \HTTP\ErrorExceptionHandler::Error(404, $message, $file, $line);
    }

    public static function underMaintenance() {

        $subject = self::getLanguage('under_maintenance');
        $message = self::getLanguage('under_maintenance_message');
        $contentType = self::getInterfaceConfig('content_type');

        Response::setStatusCode(503);

        // Build Array to output if json
        if($contentType == 'application/json') {

            $content = array(
                'error' => array(
                    'subject' => $subject,
                    'message' => $message
                ),
                'data' => array()
            );

            \HTTP\Response::output($content);

            // Build a string to output if text plain
        } elseif($contentType == 'text/plain') {

            $content = $subject . "\n";
            $content .= $message . "\n";

            \HTTP\Response::output($content);

            // Default with Template
        } else {

            Response::setTemplate(
                'under_maintenance',
                array(
                    'subject' => $subject,
                    'message' => $message,
                )
            );
            Response::output();

        }

        exit();

    }

}