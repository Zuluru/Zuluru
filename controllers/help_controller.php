<?php
class HelpController extends AppController {

	var $name = 'Help';
	var $uses = array();

	function view($controller = null, $topic = null, $item = null, $subitem = null) {
		// When we don't get a topic, it's because we're showing a group of help items.
		// This only happens in full pages. Others are expected to happen in a pop-up.
		if ($topic !== null) {
			$this->layout = 'help';
			Configure::write ('debug', 0);
		}
		$this->set(compact('controller', 'topic', 'item', 'subitem'));
		$this->set('is_coordinator', Configure::read('Zuluru.LeagueIDs') != null);
	}
}
?>