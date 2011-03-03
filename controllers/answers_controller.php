<?php
class AnswersController extends AppController {

	var $name = 'Answers';

	function activate() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		extract($this->params['named']);
		$this->set($this->params['named']);

		$success = $this->Answer->updateAll (array('active' => true), array(
				'Answer.id' => $answer,
		));
		$this->set(compact('success'));
	}

	function deactivate() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		extract($this->params['named']);
		$this->set($this->params['named']);

		$success = $this->Answer->updateAll (array('active' => 0), array(
				'Answer.id' => $answer,
		));
		$this->set(compact('success'));
	}
}
?>
