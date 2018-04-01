<?php
/**
 * Example of Extended Library in application
 */
namespace App\Lib;

class Example extends \Lib\Example {

    public static function callStatic() {
        echo "Static method called of Extended Library!";
    }

    public function multiplyNumber($multiplyTo) {
        $this->number *= $multiplyTo;
    }

}
