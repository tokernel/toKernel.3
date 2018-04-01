<?php
/**
 * Example of Library

// Call lib static
echo \Lib\Example::callStatic();

// Call App lib static
echo \App\Lib\Example::callStatic();

// Use then call
use \App\Lib\Example as Example;
echo Example::callStatic();

// Create new object then use
$nObj = new App\Lib\Example(2);
echo $nObj->getNumber();

// Another type of new object
use \App\Lib\Example as Example;
$nObj = new Example(22);
echo $nObj->getNumber();

// Another Usage Block
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
*/

namespace Lib;

class Example {

    protected $number = 0;

    public function __construct($number) {
        $this->number = $number;
    }

    /**
     * Static method
     */
	public static function callStatic() {
		echo "Static method called of Library!";
	}

    /**
     * None static method
     */
	public function callNoneStatic() {
        echo "None Static method called of Library!";
    }

    public function getNumber() {
        return $this->number;
    }

    public function setNumber($number) {
        $this->number = $number;
    }

}