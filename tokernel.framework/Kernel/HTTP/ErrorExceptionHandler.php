<?php
/**
 * Error and Exception Handler for HTTP mode.
 */

namespace HTTP;

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

            // The case if Application language file not exists.
            try {
                if ($e->getCode() == 404) {

                    $subject = \Kernel\Base\Runner::getLanguage('err_404_subject');
                    $message = \Kernel\Base\Runner::getLanguage('err_404_message');

                    $templateName = 'error_404';
                    $headerStatus = 404;

                } else {

                    $subject = \Kernel\Base\Runner::getLanguage('err_subject_production');
                    $message = \Kernel\Base\Runner::getLanguage('err_message_production');
                    $templateName = 'error';
                    $headerStatus = 500;

                }

            } catch (\Exception $e) {

                $subject = 'Error';
                $message = 'Unable to load Application language file or value!';
                $templateName = 'error';
                $headerStatus = 500;
            }

            $code = NULL;
            $file = NULL;
            $line = NULL;
            $traceStr = NULL;

        } else {

            if($e->getCode() == 404) {

                $templateName = 'error_404';
                $headerStatus = 404;

            } else {

                $templateName = 'error';
                $headerStatus = 500;

            }

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

        \HTTP\Response::noCache();
        \HTTP\Response::setStatusCode($headerStatus);

        // Get Interface output type
        $contentType = \HTTP\Runner::getInterfaceConfig('content_type');

        \HTTP\Response::setContentType($contentType);

        // Build Array to output if json
        if($contentType == 'application/json') {

            $content = array(
                'error' => array(
                    'subject' => $subject,
                    'message' => $message
                ),
                'data' => array()
            );

            if($code != '') {
                $content['error']['code'] = $code;
            }

            if($file != '') {
                $content['error']['file'] = $file;
            }

            if($line != '') {
                $content['error']['line'] = $line;
            }

            if($traceStr != '') {
                $content['error']['trace'] = $traceStr;
            }

            \HTTP\Response::setJsonContent($content);
            \HTTP\Response::output();

        } elseif($contentType == 'text/plain') {

            $content = $subject . "\n";
            $content .= $message . "\n";

            if($code != '') {
                $content .= 'Code: ' . $code . "\n";
            }

            if($file != '') {
                $content .= 'File: ' . $file . "\n";
            }

            if($line != '') {
                $content .= 'Line: ' . $line . "\n";
            }

            if($traceStr != '') {
                $content .= 'Trace: ' . $traceStr . "\n";
            }

            \HTTP\Response::setContent($content);
            \HTTP\Response::output();

        } else {

            $content = array(
                'subject' => $subject,
                'message' => $message,
            );

            if($code != '') {
                $content['message'] .= "<br />Code: " . $code;
            }

            if($file != '') {
                $content['message'] .= "<br />File: " . $file;
            }

            if($line != '') {
                $content['message'] .= "<br />Line: " . $line;
            }

            if($traceStr != '') {
                $content['message'] .= "<br />Trace: " . nl2br($traceStr);
            }

            \HTTP\Response::setTemplate(
                $templateName,
                $content
            );

            \HTTP\Response::output();

        }

        exit(1);

    }


}