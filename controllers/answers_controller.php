<?php
class AnswersController extends AppController {

	var $name = 'Answers';
	var $uses = array('Answer', 'Question');

	function isAuthorized() {
		if ($this->is_manager) {
			// Managers can perform these operations in affiliates they manage
			if (in_array ($this->params['action'], array(
					'activate',
					'deactivate',
			)))
			{
				// If an answer id is specified, check if we're a manager of that answer's affiliate
				$answer = $this->_arg('answer');
				if ($answer) {
					$question = $this->Answer->field('question_id', array('Answer.id' => $answer));
					if (in_array($this->Question->affiliate($question), $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}
		}

		return false;
	}

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
