<?php
namespace Addons\Abc\Modules;

use \Addons\Abc as ParentAddon;

class Lala extends \Base\Module {
	
	public function Hi() {
		echo "Hi from Module!";
	}
	
	public function showView() {
		
		$view = $this->loadView('Mdlview');
		echo $view->getParsed();
		
		$sv = $this->loadView('Sub1/Sub2/SubView');
		$sv->displayParsed();

		ParentAddon::getLanguage('Hello');
	}
	
	public function useModule() {
		
		$module = $this->loadModule('ModuleObjTest');
		echo "<br> Module from module: " . $module->getName() . " <br>";
		
		//$value = $this->Addon()->getConfig('name');
		//echo "<br> Value from Config " . $value . "<br>";
	}
	
}