<?php
namespace Kernel\Base;

class Runner {

    protected static $config;
    protected static $http_interface;

    private static $logFiles = array();

    private static $language = NULL;

    public static function getConfig($item, $section) {

        return self::$config->getItem($item, $section);

    }

    public static function getLanguage($item, array $lngArgs = array()) {

        if(self::$language == NULL) {

            if(TK_RUN_MODE == TK_HTTP_MODE) {
                $languagePrefix = \HTTP\Request::languagePrefix();
            } else {
                $languagePrefix = \CLI\Request::languagePrefix();
            }

            self::$language = new \Lib\Language(TK_APP_PATH . 'Languages' . TK_DS . $languagePrefix . '.ini');
        }

        return self::$language->get($item, $lngArgs);
    }

    // @todo finish this | ALMOST DONE !
    public static function log($message, $code, $file = NULL, $line = NULL) {

        $logType = \Kernel\Base\ErrorExceptionHandler::getErrorGroupName($code);

        $fileName = $logType;
        $fileName .= '.' . self::$config->getItem('log_file_extension', 'ERROR_HANDLING');

        if(!is_null($file)) {
            $message .= " | File: " . $file;
        }

        if(!is_null($line)) {
            $message .= " | Line: " . $line;
        }

        try {

            if(!isset(self::$logFiles[$logType])) {
                self::$logFiles[$logType] = new \Lib\Log($fileName);
            }

            self::$logFiles[$logType]->write($message);

        } catch(\ErrorException $e) {
            exit('Unable to write log!');
        }

    }

    public static function addonExists($addonName) {

        if(file_exists(TK_APP_PATH . 'Addons' . TK_DS . ucfirst($addonName) . '.php')) {
            return true;
        }

        return false;

    }


}