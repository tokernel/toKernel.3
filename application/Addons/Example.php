<?php
/**
 * Example Addon Controller file
 */
namespace Addons;

use \HTTP\Request as HTTPRequest;
use \HTTP\Response as HttpResponse;

use \CLI\Request as CLIRequest;
use \CLI\Response as CLIResponse;
use HTTP\Runner;

class Example extends \App\Base\MyBaseAddon {

    // Static method
    public static function callStatic() {
        echo "Called Static method of addon!";
    }

    // None static method
    public function callNoneStatic() {
        echo "Called None Static method of addon!";
    }

    public function multiplyNumber($multiplyTo) {
        $this->number *= $multiplyTo;
    }

    // Load and show View file content
    public function showView() {

        $view = $this->loadView('MyView');
        echo $view->getParsed();

    }

    public function http_index() {
        echo 'Hello! This is index page of addon! And the content type is : ' . Runner::getInterfaceConfig('content_type');
    }

    public function http_output_without_reponse() {
        echo "This is content from addon outputted without using Response Class!";
    }

    public function http_output_with_response() {
        HttpResponse::output('This is content just outputted calling: HttpResponse::output() ');
    }

    public function http_only_set_response_content() {
        HttpResponse::setContent('This is content set to response.<br>');
        HttpResponse::setContent('This is another content set to response.<br>');
    }



    public function http_test_web_page() {

        HttpResponse::setTemplate(
            'example',
            array(
                'page_title' => 'This is page title!',
                'page_message' => 'This is page message!'
            )
        );

        $view = $this->loadView(
        'MyView',
            array(
                'data' => 'Day D!'
            )
        );

        HttpResponse::noCache();
        HttpResponse::setContent('@@@Hello! This is testing web page!<br>');
        HttpResponse::setContent($view->getParsed());
        HttpResponse::setContent('###Something else here from Addon<br>');
        HttpResponse::output();

        //echo \HTTP\Request::languagePrefix();
        //throw new \ErrorException('Something HERE! EXP!');
    }

    public function http_apidata() {

        HttpResponse::setJsonContent(
            array(
                'name' => 'David',
                'company' => 'toKernel'
            )
        );

        HttpResponse::output();

    }

    public function http_ajax_web_content() {
        echo "Eccepting to ajax request!";
    }

    public function cli_test() {

        CLIResponse::output('Hello!', 'green');

        CLIResponse::output('Your first argument is: ' . CLIRequest::cliArgs(0), 'yellow');

        CLIResponse::outputColors();

        CLIResponse::outputUsage('This is my message !!!');

        CLIResponse::output(
            CLIResponse::getColoredString('David A.', 'red', 'yellow').' '.
            CLIResponse::getColoredString('and contributors', 'brown', 'light_gray')
        );

        throw new \ErrorException('Error in CLI!');

    }

}
