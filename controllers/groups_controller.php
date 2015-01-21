<?php
class GroupsController extends AppController {

	var $name = 'Groups';

	function index() {
		$this->set('groups', $this->Group->find('all'));
	}

	function activate() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		extract($this->params['named']);
		$this->set($this->params['named']);
		$name = $this->Group->field('name', array('id' => $group));

		$success = $this->Group->updateAll (array('Group.active' => true), array(
				'Group.id' => $group,
		));
		$this->set(compact('success', 'name'));
	}

	function deactivate() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		extract($this->params['named']);
		$this->set($this->params['named']);
		$name = $this->Group->field('name', array('id' => $group));

		$success = $this->Group->updateAll (array('Group.active' => 0), array(
				'Group.id' => $group,
		));
		$this->set(compact('success', 'name'));
	}
}
?>