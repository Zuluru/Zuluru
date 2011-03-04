<?php
class QuestionsController extends AppController {

	var $name = 'Questions';

	function index() {
		$this->Question->recursive = 0;
		$this->set('questions', $this->paginate('Question', array('active' => true)));
		$this->set('active', true);
	}

	function deactivated() {
		$this->Question->recursive = 0;
		$this->set('questions', $this->paginate('Question', array('active' => false)));
		$this->set('active', false);
		$this->render('index');
	}

	function view() {
		$id = $this->_arg('question');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), 'question'));
			$this->redirect(array('action' => 'index'));
		}
		$this->set('question', $this->Question->read(null, $id));
	}

	function add() {
		if (!empty($this->data)) {
			$this->Question->create();
			if ($this->Question->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), 'question'));
				$this->redirect(array('action' => 'edit', 'question' => $this->Question->id));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please, try again.', true), 'question'));
			}
		}
	}

	function edit() {
		$id = $this->_arg('question');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), 'question'));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->Question->saveAll($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), 'question'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please, try again.', true), 'question'));
			}
		}
		if (empty($this->data)) {
			$this->Question->contain(array('Answer' => array('order' => 'Answer.sort')));
			$this->data = $this->Question->read(null, $id);
		}
	}

	function activate() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		extract($this->params['named']);
		$this->set($this->params['named']);
		$name = $this->Question->field('name', array('id' => $question));

		$success = $this->Question->updateAll (array('active' => true), array(
				'Question.id' => $question,
		));
		$this->set(compact('success', 'name'));
	}

	function deactivate() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		extract($this->params['named']);
		$this->set($this->params['named']);
		$name = $this->Question->field('name', array('id' => $question));

		$success = $this->Question->updateAll (array('active' => 0), array(
				'Question.id' => $question,
		));
		$this->set(compact('success', 'name'));
	}

	function delete() {
		$id = $this->_arg('question');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid id for %s', true), 'question'));
			$this->redirect(array('action'=>'index'));
		}

		// Find all of the questionnaires that use this question
		$this->QuestionnairesQuestions = ClassRegistry::init ('QuestionnairesQuestions');
		$this->QuestionnairesQuestions->recursive = -1;
		$questionnaires = $this->QuestionnairesQuestions->find('count', array(
				'conditions' => array('question_id' => $id),
		));
		if ($questionnaires > 0) {
			$this->Session->setFlash(__('This question is used by at least one questionnaire and cannot be deleted.', true));
			$this->redirect(array('action'=>'index'));
		}

		// Wrap the whole thing in a transaction, for safety.
		$db =& ConnectionManager::getDataSource($this->Question->useDbConfig);
		$db->begin($this->Question);

		if ($this->Question->delete($id)) {
			if ($this->Question->Answer->deleteAll(array('question_id' => $id))) {
				$this->Session->setFlash(sprintf(__('%s deleted', true), 'Question'));
				$db->commit($this->Question);
				$this->redirect(array('action'=>'index'));
			}
		}

		$this->Session->setFlash(sprintf(__('%s was not deleted', true), 'Question'));
		$db->rollback($this->Question);
		$this->redirect(array('action' => 'index'));
	}

	function add_answer($id, $i) {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		$this->Question->contain(array('Answer' => array('order' => 'Answer.sort')));
		$question = $this->Question->read(null, $id);
		$sort = max (Set::extract('/Answer/sort', $question)) + 1;
		$answer = array(
			'question_id' => $id,
			'sort' => $sort,
		);
		if ($this->Question->Answer->save ($answer)) {
			$answer['id'] = $this->Question->Answer->id;
			$this->set(compact('answer', 'i'));
		}
	}

	function delete_answer() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		extract($this->params['named']);
		$this->set($this->params['named']);

		// Find if there are responses that use this answer
		$this->Response = ClassRegistry::init ('Response');
		$count = $this->Response->find('count', array(
				'conditions' => array(
					'answer_id' => $answer,
				),
		));

		// Only answers with no responses can be removed
		if ($count == 0) {
			$this->set('success', $this->Question->Answer->deleteAll (array(
					'id' => $answer,
			), false));
		} else {
			$this->set('cannot', true);
		}
	}

	function autocomplete() {
		Configure::write ('debug', 0);
		$this->Question->recursive = -1;
		$this->set('questions', $this->Question->find('all', array(
			'conditions' => array(
				'Question.question LIKE' => "%{$this->params['url']['q']}%",
				'Question.active' => true,
			),
			'fields' => array('Question.id', 'Question.question'),
			'order' => 'Question.question',
			'limit' => $this->params['url']['limit'],
		)));
		$this->layout = 'ajax';
	}

	function consolidate() {
		$this->Question->contain(array('Answer' => array('order' => 'Answer.sort')));
		$questions = $this->Question->find('all', array(
				'order' => 'Question.id',
		));
		$this->Response = ClassRegistry::init ('Response');
		$this->QuestionnairesQuestions = ClassRegistry::init ('QuestionnairesQuestions');

		$matches = array();
		foreach ($questions as $key_one => $one) {
			foreach ($questions as $key_two => $two) {
				if ($key_one < $key_two) {
					$match = $this->_compare_questions($one, $two);
					if ($match === true) {
						unset ($questions[$key_two]);
						$matches[$one['Question']['id']][$two['Question']['id']] = $this->_merge_questions ($one, $two);
					} else if ($match !== false) {
						$matches[$one['Question']['id']][$two['Question']['id']] = $match;
					}
				}
			}
		}

		$this->set(compact('matches'));
	}

	function _compare_questions($one, $two) {
		if ($one['Question']['question'] != $two['Question']['question']) return false;
		if ($one['Question']['type'] != $two['Question']['type']) return 'different type';
		if (count ($one['Answer']) != count ($two['Answer'])) return 'different answer count';
		foreach ($one['Answer'] as $key => $answer_one) {
			if (!array_key_exists ($key, $two['Answer'])) return 'missing answer';
			$answer_two = $two['Answer'][$key];
			if ($answer_one['answer'] != $answer_two['answer'])
			{
				return "answer {$answer_one['answer']} != {$answer_two['answer']}";
			}
		}

		return true;
	}

	function _merge_questions($one, $two) {
		$result =
			$this->Response->updateAll (
				array('question_id' => $one['Question']['id']),
				array('question_id' => $two['Question']['id'])
			) &&
			$this->QuestionnairesQuestions->updateAll (
				array('question_id' => $one['Question']['id']),
				array('question_id' => $two['Question']['id'])
			);

		foreach ($one['Answer'] as $key => $answer_one) {
			$answer_two = $two['Answer'][$key];
			$result &= $this->Response->updateAll (
				array('answer_id' => $answer_one['id']),
				array('answer_id' => $answer_two['id'])
			);
		}

		$result &= $this->Question->Answer->deleteAll (array('question_id' => $two['Question']['id']), false);
		$result &= $this->Question->deleteAll (array('id' => $two['Question']['id']), false);

		return ($result ? true : 'Failed to merge');
	}
}
?>
