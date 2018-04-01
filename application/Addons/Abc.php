<?php
namespace Addons;

use \Addons\Abc\Modules\ModuleObjTest as myObj;
use \Kernel\HTTP\Request;

//class Abc extends \Base\Addon {
class Abc extends \App\Base\MyBaseAddon {

    private $num;

    protected function __construct() {
        $this->num = 222;
    }

    public function getNum() {
        return $this->num;
    }

    public function hello() {
		echo "Hello From ABC!!!";
		//Response::output("Hello From ABC!!!");
	}
		
	public function useModule() {
		//$obj = new myObj();
		//return $obj->getName();
		
		//$model = new \Addons\Abc\Models\Main();
		//return $model->getRecords();
						
		//$lalaModule = new \Addons\Abc\Modules\Lala();
		//$lalaModule->showView();
		
	}
	
	public function showView() {
		
		// Load view
		$view = $this->loadView('Login');
		$view->displayParsed();
		
		// Load sub view
		$sv = $this->loadView('subDir/Some');
		$sv->displayParsed();
		
	}
	
	public function moduleViewPresent() {
		
		// Load module
		$m = $this->loadModule('Lala');
		$m->showView();
		
		// Module should load another module and use
		$m->useModule();
		
		// Load sub module
		$panel = $this->loadModule('Backend\Panel');
		$panel->runMenu();
				
	}
	
	public function modelWorks() {
		
		// Get data from model
		echo "<br> Data from model 1<br />";
		
		$model = $this->loadModel('Main');
		print_r(
			$model->getRecords()
		);
		
		echo "<br>";
		
		// Get data from su-bmodel
		echo "<br> Data from sub model<br />";
		
		$model2 = $this->loadModel('SubDB1\SubDB2\SubModel');
		print_r(
			$model2->getRecordsFromTable()
		);
		
		echo "<br>";
		
	}
	
	public function utilities() {
		echo "<br>Utilities!<br>";
		echo "Addon: " . Request::addon();
		echo "<br>";
		echo "Action: " . Request::action();
		echo "<br>";
	}
	
}
