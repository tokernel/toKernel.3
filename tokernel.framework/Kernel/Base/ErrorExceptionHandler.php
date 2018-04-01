<?php
namespace Kernel\Base;

class ErrorExceptionHandler extends \ErrorException {

    protected static function logErrorMessage($e) {

        $loggingConfigItem = 'log_unknown_errors';
        $errorGroupName = self::getErrorGroupName($e->getCode());

        // Logging the Error
        switch($errorGroupName) {

            case 'error':

                $loggingConfigItem = 'log_errors';
                break;

            case 'warning':

                $loggingConfigItem = 'log_warnings';
                break;

            case 'notice':

                $loggingConfigItem = 'log_notices';
                break;

            case 'error_404':

                $loggingConfigItem = 'log_errors_404';
                break;

            case 'exception':

                $loggingConfigItem = 'log_uncaught_exceptions';
                break;

            default:

                $loggingConfigItem = 'log_unknown_errors';
                break;
        }

        // Check if certain type of error logging is enabled.
        if(\Kernel\Base\Runner::getConfig($loggingConfigItem, 'ERROR_HANDLING') != 1) {
            return true;
        }

        // Log the Error
        \Kernel\Base\Runner::log($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine());

    }

    /**
     * Return error group by code
     *
     * @static
     * @access public
     * @param integer $errCode
     * @return string
     */
    public static function getErrorGroupName($errCode) {

        switch($errCode) {
            case E_NOTICE:
            case E_USER_NOTICE:
                $errGroup = 'notice';
                break;
            case E_WARNING:
            case E_USER_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
                $errGroup = 'warning';
                break;
            case E_ERROR:
            case E_USER_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_RECOVERABLE_ERROR:
            case E_PARSE:
            case E_STRICT:
                $errGroup = 'error';
                break;
            case 0:
                $errGroup = 'exception';
                break;
            case 404:
                $errGroup = 'error_404';
                break;
            default:
                $errGroup = 'unknown';
                break;
        } // end switch

        return $errGroup;

    } // end func getErrorGroupName

    public static function getErrorSubjectByGroupName($groupName) {

        $subject = 'Error';

        switch ($groupName) {
            case 'notice':
                $subject = 'Notice';
                break;
            case 'warning':
                $subject = 'Warning';
                break;
            case 'error':
                $subject = 'Error';
                break;
            case 'exception':
                $subject = 'Exception';
                break;
            case 'error_404':
                $subject = 'Error 404';
                break;
            default:
                $subject = 'Unknown Error';
                break;
        }

        return $subject;
    }

}