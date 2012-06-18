<?php
class GameSlotsController extends AppController {

	var $name = 'GameSlots';

	function view() {
		$id = $this->_arg('slot');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game slot', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->GameSlot->contain(array(
				'Field' => array('Facility'),
				'DivisionGameslotAvailability' => array('Division' => 'League'),
		));
		$gameSlot = $this->GameSlot->read(null, $id);
		if (!$gameSlot) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game slot', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->set(compact('gameSlot'));
	}

	function add() {
		if (!empty($this->data)) {
			// Find the list of holidays to avoid
			$holiday = ClassRegistry::init('Holiday');
			$holidays = $holiday->find('list', array('fields' => array('Holiday.date', 'Holiday.name')));
			$this->set(compact('holidays'));

			if (array_key_exists ('confirm', $this->data['GameSlot'])) {
				if (!array_key_exists ('Create', $this->data['GameSlot'])) {
					$this->Session->setFlash(__('You must select at least one game slot!', true), 'default', array('class' => 'info'));
					$this->action = 'confirm';
				} else {
					// Build the list of dates to re-use
					$weeks = array();
					$date = strtotime ($this->data['GameSlot']['game_date']);
					while (count($weeks) < $this->data['GameSlot']['weeks']) {
						if (!array_key_exists(date ('Y-m-d', $date), $holidays)) {
							$weeks[] = date ('Y-m-d', $date);
						}
						$date += WEEK;
					}

					// saveAll handles hasMany relations OR multiple records, but not both,
					// so we have to save each slot separately. Wrap the whole thing in a
					// transaction, for safety.
					$transaction = new DatabaseTransaction($this->GameSlot);
					$success = true;

					$game_end = (empty ($this->data['GameSlot']['game_end']) ? null : $this->data['GameSlot']['game_end']);
					foreach ($this->data['GameSlot']['Create'] as $field_id => $field_dates) {
						foreach (array_keys ($field_dates) as $date) {
							$slot = array(
								'GameSlot' => array(
									'field_id' => $field_id,
									'game_date' => $weeks[$date],
									'game_start' => $this->data['GameSlot']['game_start'],
									'game_end' => $game_end,
								),
								'DivisionGameslotAvailability' => array(),
							);
							foreach (array_keys ($this->data['Division']) as $division_id) {
								$slot['DivisionGameslotAvailability'][] = array('division_id' => $division_id);
							}
							// Try to save; if it fails, we need to break out of two levels of foreach
							if (!$this->GameSlot->saveAll($slot)) {
								$success = false;
							}
						}
					}

					if ($success && $transaction->commit() !== false) {
						$this->Session->setFlash(sprintf(__('The %s have been saved', true), __('game slots', true)), 'default', array('class' => 'success'));
						// We intentionally don't redirect here, leaving the user back on the
						// original "add" form, with the last game date/start/end/weeks options
						// already selected. Fields and divisions are NOT selected, because those
						// are no longer in $this->data, but that's more of a feature than a bug.
					} else {
						$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('game slots', true)), 'default', array('class' => 'warning'));
					}
				}
			// Validate the input
			} else if (!array_key_exists('Field', $this->data)) {
				$this->Session->setFlash(sprintf(__('You must select at least one %s!', true), Configure::read('ui.field')), 'default', array('class' => 'info'));
			} else if (!array_key_exists('Division', $this->data)) {
				$this->Session->setFlash(__('You must select at least one division!', true), 'default', array('class' => 'info'));
			} else {
				// By calling 'set', we deconstruct the dates from arrays to more useful strings
				$this->GameSlot->set ($this->data);
				$this->data = $this->GameSlot->data;
				$this->action = 'confirm';
			}
		}

		$id = $this->_arg('field');
		if ($id) {
			$this->GameSlot->Field->contain (array('Facility'));
			$field = $this->GameSlot->Field->read(null, $id);
			$this->set(compact('field'));
		} else {
			$regions = $this->GameSlot->Field->Facility->Region->find('all', array(
				'contain' => array(
					'Facility' => array(
						'conditions' => array(
							'Facility.is_open' => true,
						),
						'order' => 'Facility.name',
						'Field' => array(
							'conditions' => array(
								'Field.is_open' => true,
							),
							'order' => 'Field.num',
						),
					),
				),
				'order' => 'Region.id',
			));
			$this->set(compact('regions'));
		}
	}

	function edit() {
		$id = $this->_arg('slot');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game slot', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		if (!empty($this->data)) {
			// Set and then save the data back, so the date is deconstructed and can
			// be used in the readByDate call below, if required
			$this->GameSlot->set ($this->data);
			$this->data = $this->GameSlot->data;

			// The availability table isn't a standard HABTM, so we need to massage the
			// data into the correct form
			$this->data['DivisionGameslotAvailability'] = array();
			foreach ($this->data['GameSlot']['division_id'] as $division_id) {
				$this->data['DivisionGameslotAvailability'][] = array(
					'game_slot_id' => $id,
					'division_id' => $division_id,
				);
			}

			// Wrap the whole thing in a transaction, for safety.
			$transaction = new DatabaseTransaction($this->GameSlot);

			if ($this->GameSlot->DivisionGameslotAvailability->deleteAll(array('game_slot_id' => $id))) {
				if ($this->GameSlot->saveAll($this->data)) {
					$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('game slot', true)), 'default', array('class' => 'success'));
					$transaction->commit();
					$this->redirect(array('action' => 'view', 'slot' => $id));
				}
			}
			$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('game slot', true)), 'default', array('class' => 'warning'));
		}

		if (empty($this->data)) {
			$this->GameSlot->contain(array(
					'DivisionGameslotAvailability',
			));
			$this->data = $this->GameSlot->read(null, $id);
		}
		$divisions = $this->GameSlot->Game->Division->readByDate($this->data['GameSlot']['game_date']);
		$divisions = Set::combine($divisions, '{n}.Division.id', '{n}.Division.full_league_name');
		$this->data['GameSlot']['division_id'] = Set::extract ('/DivisionGameslotAvailability/division_id', $this->data);
		$this->set(compact('divisions'));
	}

	function delete() {
		$id = $this->_arg('slot');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game slot', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		// Wrap the whole thing in a transaction, for safety.
		$transaction = new DatabaseTransaction($this->GameSlot);

		if ($this->GameSlot->delete($id)) {
			if ($this->GameSlot->DivisionGameslotAvailability->deleteAll(array('game_slot_id' => $id))) {
				$this->Session->setFlash(sprintf(__('%s deleted', true), __('Game slot', true)), 'default', array('class' => 'success'));
				$transaction->commit();
				$this->redirect('/');
			}
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Game slot', true)), 'default', array('class' => 'warning'));
		$this->redirect('/');
	}
}
?>
