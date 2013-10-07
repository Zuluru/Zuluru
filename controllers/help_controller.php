<?php
class HelpController extends AppController {

	var $name = 'Help';
	var $uses = array();

	function publicActions() {
		return array('view');
	}

	function freeActions() {
		return array('view');
	}

	function view($controller = null, $topic = null, $item = null, $subitem = null) {
		$this->set(compact('controller', 'topic', 'item', 'subitem'));
		$this->set('is_coordinator', $this->UserCache->read('DivisionIDs') != null);
	}
}
?>
