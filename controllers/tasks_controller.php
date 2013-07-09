<?php
class TasksController extends AppController {

	var $name = 'Tasks';

	function isAuthorized() {
		if ($this->is_volunteer) {
			// Volunteers can can perform these operations
			if (in_array ($this->params['action'], array(
				'index',
				'view',
				'assigned',
			)))
			{
				return true;
			}
		}

		if ($this->is_manager) {
			// Managers can perform these operations
			if (in_array ($this->params['action'], array(
					'add',
			)))
			{
				return true;
			}

			// Managers can perform these operations in affiliates they manage
			if (in_array ($this->params['action'], array(
					'edit',
					'delete',
			)))
			{
				// If a task id is specified, check if we're a manager of that task's affiliate
				$task = $this->_arg('task');
				if ($task) {
					if (in_array($this->Task->affiliate($task), $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
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

		if (($this->is_admin || $this->is_manager) && $this->_arg('download')) {
			$this->RequestHandler->renderAs($this, 'csv');
			$tasks = $this->Task->Category->find('all', array(
				'conditions' => array('Category.affiliate_id' => $affiliates),
				'contain' => array(
					'Task' => array(
						'order' => array('Task.name'),
						'Person',
						'TaskSlot' => array(
							'order' => array('TaskSlot.task_date', 'TaskSlot.task_start'),
							'Person',
							'ApprovedBy',
						),
					),
				),
				'order' => array('Category.name'),
			));
			$this->set('download_file_name', 'Tasks');
			Configure::write ('debug', 0);
		} else {
			$conditions = array('Category.affiliate_id' => $affiliates);
			if (!$this->is_admin && !$this->is_manager) {
				$conditions['Task.allow_signup'] = true;
			}
			$tasks = $this->Task->find('all', array(
				'conditions' => $conditions,
				'contain' => array(
					'Category',
					'Person',
				),
				'order' => array('Category.name', 'Task.name'),
			));
		}

		$this->set(compact('tasks'));
	}

	function assigned() {
		$id = $this->Auth->user('id');
		$tasks = $this->Task->TaskSlot->find('all', array(
				'conditions' => array(
					'TaskSlot.person_id' => $id,
					'TaskSlot.task_date >= CURDATE()',
					'TaskSlot.approved' => true,
				),
				'contain' => array(
					'Task' => array(
						'Category',
						'Person',
					),
					'ApprovedBy',
				),
				'order' => array('TaskSlot.task_date', 'TaskSlot.task_start'),
		));
		return $tasks;
	}

	function view() {
		$id = $this->_arg('task');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('task', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Task->contain(array(
				'Category',
				'Person',
				'TaskSlot' => array(
					'order' => array('TaskSlot.task_date', 'TaskSlot.task_start', 'TaskSlot.task_end', 'TaskSlot.id'),
					'Person',
					'ApprovedBy',
				),
		));

		$task = $this->Task->read(null, $id);
		if (!$task) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('task', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		// TODO: if manager, check we're not looking at another affiliate
		if (!$task['Task']['allow_signup'] && !($this->is_admin || $this->is_manager)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('task', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->set(compact('task'));

		$affiliates = $this->_applicableAffiliates(true);
		if ($this->is_admin || $this->is_manager) {
			$people = $this->Task->Person->find('all', array(
					'joins' => array(
						array(
							'table' => "{$this->Person->tablePrefix}affiliates_people",
							'alias' => 'AffiliatePerson',
							'type' => 'LEFT',
							'foreignKey' => false,
							'conditions' => 'AffiliatePerson.person_id = Person.id',
						),
					),
					'conditions' => array(
						'Person.group_id' => $this->Person->Group->find('list', array(
							'conditions' => array('name' => array('Administrator', 'Volunteer')),
							'fields' => array('Group.id', 'Group.id'),
						)),
						'AffiliatePerson.affiliate_id' => array_keys($affiliates),
					),
					'contain' => array('Affiliate'),
					'order' => array('Person.first_name', 'Person.last_name'),
			));
			$people = Set::combine($people, '{n}.Person.id', '{n}.Person.full_name');
		} else {
			$my_id = $this->Auth->user('id');
		}
		$this->set(compact('affiliates', 'people', 'my_id'));
	}

	function add() {
		if (!empty($this->data)) {
			$this->Task->create();
			if ($this->Task->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('task', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('task', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->Task->Category->affiliate($this->data['Task']['category_id']));
			}
		}
		$affiliates = $this->_applicableAffiliates(true);
		$categories = $this->Task->Category->find('list', array('order' => 'Category.name'));
		$people = $this->Task->Person->find('all', array(
				'joins' => array(
					array(
						'table' => "{$this->Person->tablePrefix}affiliates_people",
						'alias' => 'AffiliatePerson',
						'type' => 'LEFT',
						'foreignKey' => false,
						'conditions' => 'AffiliatePerson.person_id = Person.id',
					),
				),
				'conditions' => array(
					'Person.group_id' => $this->Person->Group->find('list', array(
						'conditions' => array('name' => array('Administrator', 'Volunteer')),
						'fields' => array('Group.id', 'Group.id'),
					)),
					'AffiliatePerson.affiliate_id' => array_keys($affiliates),
				),
				'contain' => array('Affiliate'),
				'order' => array('Person.first_name', 'Person.last_name'),
		));
		$people = Set::combine($people, '{n}.Person.id', '{n}.Person.full_name');
		$this->set(compact('affiliates', 'categories', 'people'));
		$this->set('add', true);

		$this->render ('edit');
	}

	function edit() {
		$id = $this->_arg('task');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('task', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->Task->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('task', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('task', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->Task->affiliate($id));
			}
		}
		if (empty($this->data)) {
			$this->Task->contain('Category');
			$this->data = $this->Task->read(null, $id);
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('task', true)), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'index'));
			}
			$this->Configuration->loadAffiliate($this->data['Category']['affiliate_id']);
		}
		$affiliates = $this->_applicableAffiliates(true);
		$categories = $this->Task->Category->find('list', array('order' => 'Category.name'));
		$people = $this->Task->Person->find('all', array(
				'joins' => array(
					array(
						'table' => "{$this->Person->tablePrefix}affiliates_people",
						'alias' => 'AffiliatePerson',
						'type' => 'LEFT',
						'foreignKey' => false,
						'conditions' => 'AffiliatePerson.person_id = Person.id',
					),
				),
				'conditions' => array(
					'Person.group_id' => $this->Person->Group->find('list', array(
						'conditions' => array('name' => array('Administrator', 'Volunteer')),
						'fields' => array('Group.id', 'Group.id'),
					)),
					'AffiliatePerson.affiliate_id' => array_keys($affiliates),
				),
				'contain' => array('Affiliate'),
				'order' => array('Person.first_name', 'Person.last_name'),
		));
		$people = Set::combine($people, '{n}.Person.id', '{n}.Person.full_name');
		$this->set(compact('affiliates', 'categories', 'people'));
	}

	function delete() {
		$id = $this->_arg('task');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('task', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$dependencies = $this->Task->dependencies($id);
		if ($dependencies !== false) {
			$this->Session->setFlash(__('The following records reference this task, so it cannot be deleted.', true) . '<br>' . $dependencies, 'default', array('class' => 'warning'));
			$this->redirect(array('action' => 'index'));
		}
		if ($this->Task->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), __('Task', true)), 'default', array('class' => 'success'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Task', true)), 'default', array('class' => 'warning'));
		$this->redirect(array('action' => 'index'));
	}

}
?>