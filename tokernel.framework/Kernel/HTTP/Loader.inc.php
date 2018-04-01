<?php

namespace HTTP;

/* Define new line character */
define('TK_NL', "<br />");

/* Define application run mode */
define('TK_RUN_MODE', TK_HTTP_MODE);

use \HTTP\Request as Request;
use \HTTP\Response as Response;
use \HTTP\Routing as Routing;

/* Check Required PHP Version to run Framework. */
if(version_compare(PHP_VERSION, TK_PHP_VERSION_REQUIRED, '<')) {
	Response::setStatusCode(500);
	Response::setContent('toKernel by David A. and contributors v' . TK_VERSION . '.' . TK_NL);
	Response::setContent('PHP Version ' . PHP_VERSION . ' is not compatible.' . TK_NL);
	Response::setContent('Version ' . TK_PHP_VERSION_REQUIRED . ' or newer Required.' . TK_NL);
	Response::output();
	exit(1);
}

// Set Error handling to Exception
set_error_handler('\HTTP\ErrorExceptionHandler::Error');

register_shutdown_function(function() {

    $error = error_get_last();

    if($error) {

        // \HTTP\ErrorExceptionHandler::ErrorHandler($error["type"], $error["message"], $error["file"], $error["line"]);
        \HTTP\ErrorExceptionHandler::DisplayError(new \ErrorException($error["message"], $error["type"], 0, $error["file"], $error["line"]));
        exit(1);
    }

});

\HTTP\Runner::run();

//try {

// @todo remove from here

    //echo abc();
    /* Example of lib usage */
    /*
    echo "\n";
    // Call Static method of Library
    App\Lib\Example::callStatic();

    // Create Library object
    $objNumeric = new App\Lib\Example(1);
    echo "\n";
    echo $objNumeric->getNumber();
    $objNumeric->setNumber(5);
    echo "\n";
    echo $objNumeric->getNumber();
    $objNumeric->multiplyNumber(10);
    echo "\n";
    echo $objNumeric->getNumber();

    echo "\n";
    // Call lib static
    echo \Lib\Example::callStatic();
    echo "\n";
    // Call App lib static
    echo \App\Lib\Example::callStatic();
    echo "\n";
    // Use then call
    use \App\Lib\Example as Example;

    echo Example::callStatic();
    echo "\n";
    // Create new object than use
    $nObj = new App\Lib\Example(2);
    echo $nObj->getNumber();
    echo "\n";
    // Another type of new object
    //use \App\Lib\Example as Example;
    $nObj = new Example(22);
    echo $nObj->getNumber();
    echo "\n";
    */

    /*
    // Another Usage of libraries
    echo \Lib\Test::a();
    echo "\n";

    echo \App\Lib\Test::a();

    echo "\n";

    echo \App\Lib\Test::b();
    echo "\n";

    use \App\Lib\Test as Test;

    echo Test::a();
    echo "\n";

    echo Test::b();
    echo "\n";

    $nObj = new App\Lib\Test;
    echo $nObj->getNumber();
    */

    /*
// Example of addon usage
    echo "\nUsing Addon \n";

// Call Static method of Addon
    \Addons\Example::callStatic();
    echo "\n";

// use then call static
    //use \Addons\Example as Example;

    \Addons\Example::callStaticInBase();
    echo "\n";

// Get Addon instance as an object
    $myAddonObj = \Addons\Example::instance();
    $myAddonObj->callNoneStatic();
    echo "\n";

    $myAddonObj->setNumber(4);
    echo $myAddonObj->getNumber();
    echo "\n";

    $myAddonObj->setNumber(11);
    echo $myAddonObj->getNumber();
    echo "\n";

// Getting new instance will return same object of Addon class.
    $myOther = \Addons\Example::instance();
    echo $myOther->getNumber();
    echo "\n";

    $myOther->multiplyNumber(10);
    echo $myOther->getNumber();
    echo "\n";

    $k = \Addons\Abc::instance();
    $k->showView();

    $j = \Addons\Abc::instance();
    $j->moduleViewPresent();

    echo "\n";
    echo \Addons\Abc::instance()->getNum();

    $myAddonObj->showView();

    echo "\n";
    echo "Config value Example addon: " . $myOther->getConfig('name');
    echo "\n";
    echo "Config value ABC addon: " . $k->getConfig('name');
    echo "\n";

    echo "Config value Example addon: " . $myOther->getConfig('path');
    echo "\n";
    echo "Config value ABC addon: " . $k->getConfig('path');
    echo "\n";

    echo "Config value Example addon: " . $myOther->getConfig('main', 'CORE');
    echo "\n";
    echo "Config value ABC addon: " . $k->getConfig('main', 'CORE');
    echo "\n";

    echo "Config value Example addon: " . $myOther->getConfig('second', 'CORE');
    echo "\n";
    echo "Config value ABC addon: " . $k->getConfig('second', 'CORE');
    echo "\n";

    //throw new ErrorException('This is ErrorException message!');

    //throw new Exception('This is Exception message!');

    //trigger_error('Test notice message!', E_USER_NOTICE);

    //echo $abs['www'];

    //abc();

    //file_get_contents('/etc/shadow');

    //trigger_error('Test Triggered Warning !', E_USER_WARNING);

    /*
    echo \Lib\Client::browserName();
    echo "\n";

    echo \Lib\Client::platformName();
    echo "\n";

    echo "Request!";
    echo "\n";
    echo Request::languagePrefix();
    echo "\n";
    echo Request::baseUrl();
    echo "\n";

    /*
    $a = \Addons\Abc::instance();
    $a->showView();
    $a->moduleViewPresent();
    $a->useModule();
    $a->modelWorks();
    $a->utilities();
    $a->testAppBase();
    */

    /*
    echo "\n\nEND!\n";

    $content = ob_get_clean();

    echo $content;

} catch (\HTTP\DisplayErrorException $e) {
    \HTTP\ErrorExceptionHandler::DisplayError($e);
} catch (\ErrorException $e) {
    \HTTP\ErrorExceptionHandler::DisplayError($e);
} catch(\Exception $e) {
    \HTTP\ErrorExceptionHandler::DisplayError($e);
}


*/
