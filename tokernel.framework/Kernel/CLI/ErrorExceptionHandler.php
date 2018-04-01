<?php
/**
 * Error and Exception Handler for CLI mode.
 */
namespace CLI;

class ErrorExceptionHandler extends \Kernel\Base\ErrorExceptionHandler {

    public function __construct($errorCode, $errorMessage, $errorFile, $errorLine) {

        parent::__construct($errorMessage, $errorCode);

        $this->file = $errorFile;
        $this->line = $errorLine;

    }

    public static function Error($code, $message, $file, $line) {

        ErrorExceptionHandler::DisplayError(new \ErrorException($message, $code, 0, $file, $line));

    }

    public static function DisplayError($e) {

        // Logging
        self::logErrorMessage($e);

        // Check Error and Exception display options
        $errType = self::getErrorGroupName($e->getCode());

        if($errType == 'notice' and \HTTP\Runner::getConfig('show_notices', 'ERROR_HANDLING') != 1) {
            return true;
        }

        if($errType == 'warning' and \HTTP\Runner::getConfig('show_warnings', 'ERROR_HANDLING') != 1) {
            return true;
        }

        if($errType == 'error' and \HTTP\Runner::getConfig('show_errors', 'ERROR_HANDLING') != 1) {
            return true;
        }

        if($errType == 'unknown' and \HTTP\Runner::getConfig('show_unknown_errors', 'ERROR_HANDLING') != 1) {
            return true;
        }

        if($errType == 'exception' and \HTTP\Runner::getConfig('show_uncaught_exceptions', 'ERROR_HANDLING') != 1) {
            return true;
        }

        // Check, if in production mode, then display User friendly Error message without details.
        if(\Kernel\Base\Runner::getConfig('app_mode', 'RUN_MODE') == 'production') {

            $subject = \Kernel\Base\Runner::getLanguage('err_subject_production');
            $message = \Kernel\Base\Runner::getLanguage('err_message_production');

            $code = NULL;
            $file = NULL;
            $line = NULL;
            $traceStr = NULL;

        } else {

            $subject = \Kernel\Base\ErrorExceptionHandler::getErrorSubjectByGroupName($errType);
            $message = $e->getMessage();
            $code = $e->getCode();
            $file = $e->getFile();
            $line = $e->getLine();
            $traceStr = $e->getTraceAsString();

        }

        // Clean all buffer levels.
        while(ob_get_level()) {
            ob_end_clean();
        }

        // Output copyright info
        $output = '';

        $output .= TK_NL." -";
        $output .= TK_NL." | toKernel by David A. and contributors v".TK_VERSION;
        $output .= TK_NL." | Copyright (c) " . date('Y') . " toKernel <framework@tokernel.com>";
        $output .= TK_NL." | ";
        $output .= TK_NL." | Running in " . php_uname();
        $output .= TK_NL." - ";

        \CLI\Response::output($output, 'red');
        \CLI\Response::output('');

        \CLI\Response::output(' ', null, null, false);
        \CLI\Response::output(' ' . $subject . ' ', 'white', 'red', false);
        \CLI\Response::output(' ', null, null, true);

        \CLI\Response::output('');
        \CLI\Response::output(' ' . $message, 'red');


        if($code != '') {
            \CLI\Response::output(' Code: ' . $code, 'yellow');
        }

        if($file != '') {
            \CLI\Response::output(' File: ' . $file, 'yellow');
        }

        if($line != '') {
            \CLI\Response::output(' Line: ' . $line, 'yellow');
        }

        if($traceStr != '') {
            \CLI\Response::output(' Trace: ' . $traceStr, 'yellow');
        }

        exit(1);

    }

}