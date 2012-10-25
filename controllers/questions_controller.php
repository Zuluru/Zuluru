<?php
class QuestionsController extends AppController {

	var $name = 'Questions';
	var $paginate = array(
		'contain' => array('Affiliate'),
		'order' => array('Affiliate.name'),
	);

	function isAuthorized() {
		if ($this->is_manager) {
			// Managers can perform these operations
			if (in_array ($this->params['action'], array(
					'index',
					'deactivated',
					'add',
					'autocomplete',
			)))
			{
				return true;
			}

			// Managers can perform these operations in affiliates they manage
			if (in_array ($this->params['action'], array(
					'view',
					'edit',
					'add_answer',
					'activate',
					'deactivate',
					'delete',
			)))
			{
				// If a question id is specified, check if we're a manager of that question's affiliate
				$question = $this->_arg('question');
				if ($question) {
					if (in_array($this->Question->affiliate($question), $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}

			if (in_array ($this->params['action'], array(
					'delete_answer',
			)))
			{
				// If an answer id is specified, check if we're a manager of that answer's affiliate
				$answer = $this->_arg('answer');
				if ($answer) {
					$question = $this->Question->Answer->field('question_id', array('Answer.id' => $answer));
					if (in_array($this->Question->affiliate($question), $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}
		}

		return false;
	}

	function index() {
		$this->Question->recursive = 0;

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->paginate['conditions'] = array(
			'Question.active' => true,
			'Question.affiliate_id' => $affiliates,
		);

		$this->set('questions', $this->paginate('Question'));
		$this->set('active', true);
		$this->set(compact('affiliates'));
	}

	function deactivated() {
		$this->Question->recursive = 0;

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->paginate['conditions'] = array(
			'Question.active' => false,
			'Question.affiliate_id' => $affiliates,
		);

		$this->set('questions', $this->paginate('Question'));
		$this->set('active', false);
		$this->set(compact('affiliates'));
		$this->render('index');
	}

	function view() {
		$id = $this->_arg('question');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('question', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Question->contain('Answer', 'Affiliate');
		$question = $this->Question->read(null, $id);
		if (!$question) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('question', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Configuration->loadAffiliate($question['Question']['affiliate_id']);

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('question', 'affiliates'));
	}

	function add() {
		if (!empty($this->data)) {
			$this->Question->create();
			if ($this->Question->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('question', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'edit', 'question' => $this->Question->id));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('question', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->data['Question']['affiliate_id']);
			}
		}

		$this->set('affiliates', $this->_applicableAffiliates(true));

		if (Configure::read('feature.tiny_mce')) {
			$this->helpers[] = 'TinyMce.TinyMce';
		}
	}

	function edit() {
		$id = $this->_arg('question');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('question', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->Question->saveAll($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('question', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('question', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->Question->affiliate($id));
			}
		}
		if (empty($this->data)) {
			$this->Question->contain(array('Answer' => array('order' => 'Answer.sort')));
			$this->data = $this->Question->read(null, $id);
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('question', true)), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'index'));
			}
			$this->Configuration->loadAffiliate($this->data['Question']['affiliate_id']);
		}

		$this->set('affiliates', $this->_applicableAffiliates(true));

		if (Configure::read('feature.tiny_mce')) {
			$this->helpers[] = 'TinyMce.TinyMce';
		}
	}

	function activate() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		extract($this->params['named']);
		$this->set($this->params['named']);
		$name = $this->Question->field('name', array('id' => $question));

		$success = $this->Question->updateAll (array('Question.active' => true), array(
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

		$success = $this->Question->updateAll (array('Question.active' => 0), array(
				'Question.id' => $question,
		));
		$this->set(compact('success', 'name'));
	}

	function delete() {
		$id = $this->_arg('question');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('question', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action'=>'index'));
		}
		$dependencies = $this->Question->dependencies($id);
		if ($dependencies !== false) {
			$this->Session->setFlash(__('The following records reference this question, so it cannot be deleted.', true) . '<br>' . $dependencies, 'default', array('class' => 'warning'));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Question->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), __('Question', true)), 'default', array('class' => 'success'));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Question', true)), 'default', array('class' => 'warning'));
		$this->redirect(array('action' => 'index'));
	}

	function add_answer($i) {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		$id = $this->_arg('question');
		$this->Question->contain(array('Answer' => array('order' => 'Answer.sort')));
		$question = $this->Question->read(null, $id);
		$sort = max (Set::extract('/Answer/sort', $question)) + 1;
		$answer = array(
			'question_id' => $id,
			'sort' => $sort,
		);
		if ($this->Question->Answer->save ($answer)) {
			$answer['id'] = $this->Question->Answer->id;
			$answer['active'] = true;
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

		$conditions = array(
			'Question.question LIKE' => "%{$this->params['url']['q']}%",
			'Question.active' => true,
		);
		$affiliate = $this->_arg('affiliate');
		if ($affiliate && ($this->is_admin || in_array($affiliate, $this->Session->read('Zuluru.ManagedAffiliateIDs')))) {
			$conditions['Question.affiliate_id'] = $affiliate;

			$this->set('questions', $this->Question->find('all', array(
				'conditions' => $conditions,
				'contain' => array(),
				'fields' => array('Question.id', 'Question.question'),
				'order' => 'Question.question',
				'limit' => $this->params['url']['limit'],
			)));
		} else {
			$this->set('questions', array());
		}
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
