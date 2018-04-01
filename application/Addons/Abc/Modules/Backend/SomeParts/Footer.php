<?php
namespace Addons\Abc\Modules\Backend\SomeParts;

class Footer Extends \Base\Module {
	
	public function runFooter() {
		$f = $this->loadView('F');
		$f->displayParsed();
	}
}