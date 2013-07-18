<?php
class TaskSlotsController extends AppController {

	var $name = 'TaskSlots';

	function publicActions() {
		return array('ical');
	}

	function isAuthorized() {
		if ($this->is_volunteer) {
			// Volunteers can can perform these operations
			if (in_array ($this->params['action'], array(
				'assign',
			)))
			{
				// If a person id is specified, check if we're that person
				$person = $this->_arg('person');
				if ($person && $person == $this->Auth->user('id')) {
					return true;
				}
			}
		}

		if ($this->is_manager) {
			// Managers can perform these operations
			if (in_array ($this->params['action'], array(
					'index',
					'add',
			)))
			{
				return true;
			}

			// Managers can perform these operations in affiliates they manage
			if (in_array ($this->params['action'], array(
					'view',
					'edit',
					'assign',
					'approve',
					'delete',
			)))
			{
				// If a task slot id is specified, check if we're a manager of that task slot's affiliate
				$taskSlot = $this->_arg('slot');
				if ($taskSlot) {
					if (in_array($this->TaskSlot->affiliate($taskSlot), $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}
		}
		return false;
	}

	function index() {
		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));

		$this->TaskSlot->recursive = 0;
		$this->set('taskSlots', $this->paginate());
	}

	function view() {
		$id = $this->_arg('slot');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('task slot', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'tasks', 'action' => 'index'));
		}
		$this->TaskSlot->contain (array (
			'Task' => 'Category',
			'Person',
			'ApprovedBy',
		));
		$taskSlot = $this->TaskSlot->read(null, $id);
		$this->Configuration->loadAffiliate($taskSlot['Task']['Category']['affiliate_id']);
		$this->set(compact('taskSlot'));
	}

	// This function takes the parameters the old-fashioned way, to try to be more third-party friendly
	function ical($slot_id) {
		$this->layout = 'ical';
		if (!$slot_id) {
			return;
		}

		$this->TaskSlot->contain (array (
			'Task' => array('Category', 'Person'),
		));
		$taskSlot = $this->TaskSlot->read(null, $slot_id);
		if (!$taskSlot || !$taskSlot['TaskSlot']['approved']) {
			return;
		}
		$this->Configuration->loadAffiliate($taskSlot['Task']['Category']['affiliate_id']);

		$this->set('calendar_type', 'Task');
		$this->set('calendar_name', 'Task');
		$this->set(compact('taskSlot'));

		Configure::write ('debug', 0);
	}

	function add() {
		$id = $this->_arg('task');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('task', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'tasks', 'action' => 'index'));
		}

		if (!empty($this->data)) {
			$this->data['TaskSlot']['task_id'] = $id;
			$this->TaskSlot->set($this->data);
			if ($this->TaskSlot->validates($this->data)) {
				$save = array();
				for ($days = 0; $days < $this->data['TaskSlot']['days_to_repeat']; ++ $days) {
					for ($slots = 0; $slots < $this->data['TaskSlot']['number_of_slots']; ++ $slots) {
						$save[] = $this->TaskSlot->data['TaskSlot'];
					}
					$this->TaskSlot->data['TaskSlot']['task_date'] = date('Y-m-d', strtotime($this->TaskSlot->data['TaskSlot']['task_date']) + DAY);
				}
				if ($this->TaskSlot->saveAll($save)) {
					$this->Session->setFlash(__('The task slot(s) have been saved. You may create more similar task slots below.', true), 'default', array('class' => 'success'));
				} else {
					$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('task slot(s)', true)), 'default', array('class' => 'warning'));
					$this->Configuration->loadAffiliate($this->TaskSlot->Task->affiliate($id));
				}
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('task slot(s)', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->TaskSlot->Task->affiliate($id));
			}
		}

		$this->TaskSlot->Task->contain();
		$task = $this->TaskSlot->Task->read(null, $id);
		if (!$task) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('task', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'tasks', 'action' => 'index'));
		}

		$affiliates = $this->_applicableAffiliates(true);
		$this->set(compact('affiliates', 'task'));
	}

	function edit() {
		$id = $this->_arg('slot');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('task slot', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'tasks', 'action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->TaskSlot->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('task slot', true)), 'default', array('class' => 'success'));
				$this->redirect(array('controller' => 'tasks', 'action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('task slot', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->TaskSlot->affiliate($id));
			}
		}
		if (empty($this->data)) {
			$this->TaskSlot->contain();
			$this->data = $this->TaskSlot->read(null, $id);
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('region', true)), 'default', array('class' => 'info'));
				$this->redirect(array('controller' => 'tasks', 'action' => 'index'));
			}
			$this->Configuration->loadAffiliate($this->TaskSlot->affiliate($id));
		}
		$affiliates = $this->_applicableAffiliates(true);
		$tasks = $this->TaskSlot->Task->find('list');
		$this->set(compact('affiliates', 'tasks'));
	}

	function delete() {
		$id = $this->_arg('slot');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('task slot', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'tasks', 'action' => 'index'));
		}
		$dependencies = $this->TaskSlot->dependencies($id);
		if ($dependencies !== false) {
			$this->Session->setFlash(__('The following records reference this task slot, so it cannot be deleted.', true) . '<br>' . $dependencies, 'default', array('class' => 'warning'));
			$this->redirect(array('controller' => 'tasks', 'action' => 'index'));
		}
		if ($this->TaskSlot->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), __('Task slot', true)), 'default', array('class' => 'success'));
			$this->redirect(array('controller' => 'tasks', 'action' => 'index'));
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Task slot', true)), 'default', array('class' => 'warning'));
		$this->redirect(array('controller' => 'tasks', 'action' => 'index'));
	}

	function assign() {
		$id = $this->_arg('slot');
		if (!$id) {
			if ($this->RequestHandler->isAjax()) {
				$this->set(array('error' => sprintf(__('Invalid %s', true), __('task slot', true))));
				return;
			} else {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('task slot', true)), 'default', array('class' => 'info'));
				$this->redirect(array('controller' => 'tasks', 'action' => 'index'));
			}
		}
		$this->TaskSlot->contain('Task');
		$taskSlot = $this->TaskSlot->read(null, $id);
		if (!$taskSlot) {
			if ($this->RequestHandler->isAjax()) {
				$this->set(array('error' => sprintf(__('Invalid %s', true), __('task slot', true))));
				return;
			} else {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('task slot', true)), 'default', array('class' => 'info'));
				$this->redirect(array('controller' => 'tasks', 'action' => 'index'));
			}
		}

		if (!$taskSlot['Task']['allow_signup'] && !($this->is_admin || $this->is_manager)) {
			if ($this->RequestHandler->isAjax()) {
				$this->set(array('error' => sprintf(__('Invalid %s', true), __('task', true))));
				return;
			} else {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('task', true)), 'default', array('class' => 'info'));
				$this->redirect(array('controller' => 'tasks', 'action' => 'view', 'task' => $taskSlot['Task']['id']));
			}
		}

		if ($taskSlot['TaskSlot']['person_id'] && !($this->is_admin || $this->is_manager)) {
			if ($this->RequestHandler->isAjax()) {
				$this->set(array('error' => __('This task slot has already been assigned.', true)));
				return;
			} else {
				$this->Session->setFlash(__('This task slot has already been assigned.', true), 'default', array('class' => 'info'));
				$this->redirect(array('controller' => 'tasks', 'action' => 'view', 'task' => $taskSlot['Task']['id']));
			}
		}

		$person_id = $this->_arg('person');
		if ($person_id === null || $person_id === '') {
			if ($this->RequestHandler->isAjax()) {
				$this->set(array('error' => sprintf(__('Invalid %s', true), __('person', true))));
				return;
			} else {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('person', true)), 'default', array('class' => 'info'));
				$this->redirect(array('controller' => 'tasks', 'action' => 'view', 'task' => $taskSlot['Task']['id']));
			}
		}
		if ($person_id != 0) {
			$this->TaskSlot->Person->contain(false);
			$person = $this->TaskSlot->Person->read(null, $person_id);
			if (!$person) {
				if ($this->RequestHandler->isAjax()) {
					$this->set(array('error' => sprintf(__('Invalid %s', true), __('person', true))));
					return;
				} else {
					$this->Session->setFlash(sprintf(__('Invalid %s', true), __('person', true)), 'default', array('class' => 'info'));
					$this->redirect(array('controller' => 'tasks', 'action' => 'view', 'task' => $taskSlot['Task']['id']));
				}
			}
		}

		$this->set(compact('id', 'taskSlot', 'person', 'person_id'));

		if (!empty($person_id)) {
			$conflict = $this->TaskSlot->find('first', array(
					'contain' => array('Task' => 'Category'),
					'conditions' => array(
						'TaskSlot.id !=' => $id,
						'TaskSlot.person_id' => $person_id,
						'TaskSlot.task_date' => $taskSlot['TaskSlot']['task_date'],
						'OR' => array(
							array(
								'TaskSlot.task_start >=' => $taskSlot['TaskSlot']['task_start'],
								'TaskSlot.task_start <' => $taskSlot['TaskSlot']['task_end'],
							),
							array(
								'TaskSlot.task_start <' => $taskSlot['TaskSlot']['task_start'],
								'TaskSlot.task_end >' => $taskSlot['TaskSlot']['task_start'],
							),
						),
					),
			));
			if (!empty($conflict)) {
				App::import('Helper', 'Time');
				App::import('Helper', 'ZuluruTime');
				$time = new ZuluruTimeHelper();
				if ($this->RequestHandler->isAjax()) {
					$this->set(array(
						'error' => sprintf(__('This person has a conflicting assignment:\n\n%s (%s) from %s to %s on %s', true),
									$conflict['Task']['name'], $conflict['Task']['Category']['name'],
									$time->time($conflict['TaskSlot']['task_start']),
									$time->time($conflict['TaskSlot']['task_end']),
									$time->date($conflict['TaskSlot']['task_date'])),
						'reset' => true,
					));
					return;
				} else {
					$this->Session->setFlash(sprintf(__('This person has a conflicting assignment: %s (%s) from %s to %s on %s', true),
									$conflict['Task']['name'], $conflict['Task']['Category']['name'],
									$time->time($conflict['TaskSlot']['task_start']),
									$time->time($conflict['TaskSlot']['task_end']),
									$time->date($conflict['TaskSlot']['task_date'])), 'default', array('class' => 'info'));
					$this->redirect(array('controller' => 'tasks', 'action' => 'view', 'task' => $taskSlot['Task']['id']));
				}
			}
		}

		if ($taskSlot['Task']['auto_approve'] || $this->is_admin || $this->is_manager) {
			$update = array(
				'approved' => true,
				'approved_by' => $this->Auth->user('id'),
			);
			$this->TaskSlot->Person->contain();
			$this->set('approved_by', $this->TaskSlot->Person->read(null, $this->Auth->user('id')));
		} else {
			$update = array(
				'approved' => false,
				'approved_by' => null,
			);
		}

		if (empty($person_id)) {
			$update['person_id'] = null;
			$update['approved'] = false;
			$update['approved_by'] = null;
		} else {
			$update['person_id'] = $person_id;
		}

		if (!$this->TaskSlot->save($update)) {
			$this->set(array('error' => __('Error assigning the task slot', true)));
			return;
		} else {
			if ($update['approved'] && $taskSlot['TaskSlot']['person_id'] == $this->my_id) {
				$this->Session->delete('Zuluru.Tasks');
			}
			if (!$this->RequestHandler->isAjax()) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('assignment', true)), 'default', array('class' => 'success'));
				$this->redirect(array('controller' => 'tasks', 'action' => 'view', 'task' => $taskSlot['Task']['id']));
			}
		}
	}

	function approve() {
		$id = $this->_arg('slot');
		if (!$id) {
			if ($this->RequestHandler->isAjax()) {
				$this->set(array('error' => sprintf(__('Invalid %s', true), __('task slot', true))));
				return;
			} else {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('task slot', true)), 'default', array('class' => 'info'));
				$this->redirect(array('controller' => 'tasks', 'action' => 'index'));
			}
		}
		$this->TaskSlot->contain('Task');
		$taskSlot = $this->TaskSlot->read(null, $id);
		if (!$taskSlot) {
			if ($this->RequestHandler->isAjax()) {
				$this->set(array('error' => sprintf(__('Invalid %s', true), __('task slot', true))));
				return;
			} else {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('task slot', true)), 'default', array('class' => 'info'));
				$this->redirect(array('controller' => 'tasks', 'action' => 'index'));
			}
		}

		if (!$taskSlot['TaskSlot']['person_id']) {
			if ($this->RequestHandler->isAjax()) {
				$this->set(array('error' => __('This task slot has not been assigned.', true)));
				return;
			} else {
				$this->Session->setFlash(__('This task slot has not been assigned.', true), 'default', array('class' => 'info'));
				$this->redirect(array('controller' => 'tasks', 'action' => 'view', 'task' => $taskSlot['Task']['id']));
			}
		}

		if ($taskSlot['TaskSlot']['approved']) {
			if ($this->RequestHandler->isAjax()) {
				$this->set(array('error' => __('This task slot has already been approved.', true)));
				return;
			} else {
				$this->Session->setFlash(__('This task slot has already been approved.', true), 'default', array('class' => 'info'));
				$this->redirect(array('controller' => 'tasks', 'action' => 'view', 'task' => $taskSlot['Task']['id']));
			}
		}

		$this->set(compact('id', 'taskSlot'));

		$update = array(
			'approved' => true,
			'approved_by' => $this->Auth->user('id'),
		);
		$this->TaskSlot->Person->contain();
		$this->set('approved_by', $this->TaskSlot->Person->read(null, $this->Auth->user('id')));

		if (!$this->TaskSlot->save($update)) {
			$this->set(array('error' => __('Error approving the task slot', true)));
			return;
		} else if (!$this->RequestHandler->isAjax()) {
			$this->Session->setFlash(sprintf(__('The %s has been approved', true), __('assignment', true)), 'default', array('class' => 'success'));
			$this->redirect(array('controller' => 'tasks', 'action' => 'view', 'task' => $taskSlot['Task']['id']));
		}
	}
}
?>