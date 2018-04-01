<?php
// Base Addon in Application
namespace App\Base;

class MyBaseAddon extends \Base\Addon {

    protected $number;

    protected function __construct() {

    }

    public static function callStaticInBase() {
        echo "Called Static method of MyBaseAddon!";
    }

    public function setNumber($number) {
        $this->number = $number;
    }

    public function getNumber() {
        return $this->number;
    }

}