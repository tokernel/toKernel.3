<?php
namespace Addons\Abc\Modules;

class ModuleObjTest extends \Base\Module {
	
	private $name = 'OOO';
	
	public function __construct()
	{
		$this->name = 'OAO';
	}
	
	public function getName() {
		
		$f = $this->loadModule('Backend\SomeParts\Footer');
		$f->runFooter();
		
		return $this->name;
	}
	
}