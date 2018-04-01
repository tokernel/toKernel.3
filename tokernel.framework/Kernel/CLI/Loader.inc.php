<?php

namespace CLI;

/* Define new line character */
define('TK_NL', "\n");

/* Define Framework's run mode */
define('TK_RUN_MODE', TK_CLI_MODE);

/*
 * Execute forever for CLI.
 *
 * Note: The default limit is 30 seconds, defined in php.ini
 * max_execution_time = 30
 *
 * Note: This function has no effect when PHP is running in safe mode.
 * There is no workaround other than turning off safe mode or changing
 * the time limit in the php.ini.
 */
set_time_limit(0);

/* Set some configurations for CLI mode */
ini_set('track_errors', true);
ini_set('html_errors', false);

use \CLI\Request as Request;
use \CLI\Response as Response;
use \CLI\Routing as Routing;

/* Check Required PHP Version to run Framework. */
if(version_compare(PHP_VERSION, TK_PHP_VERSION_REQUIRED, '<')) {
	Response::output('toKernel by David A. and contributors v'.TK_VERSION, 'red');
	Response::output('PHP Version ' . PHP_VERSION . ' is not compatible.', 'red');
	Response::output('Version ' . TK_PHP_VERSION_REQUIRED . ' or newer Required.');
	exit(1);
}

// Set Error handling to Exception
set_error_handler('\CLI\ErrorExceptionHandler::Error');

register_shutdown_function(function() {

    $error = error_get_last();

    if($error) {

        \CLI\ErrorExceptionHandler::DisplayError(new \ErrorException($error["message"], $error["type"], 0, $error["file"], $error["line"]));
        exit(1);
    }

});

\CLI\Runner::run($argv);

/*
$conf = new \Lib\Ini(TK_APP_PATH . 'Config/application.ini', 'CLI');

Response::init();

// Initialize request
Request::init(c, $conf);

// @todo remove from here
// Usage:

echo "Using Addon \n";

\Addons\Example::instance()->testCLI();

echo "\n";

echo "\n\nEND!\n";
*/