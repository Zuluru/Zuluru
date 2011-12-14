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
				'Field' => array('ParentField'),
				'LeagueGameslotAvailability' => array('League'),
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
								'LeagueGameslotAvailability' => array(),
							);
							foreach (array_keys ($this->data['League']) as $league_id) {
								$slot['LeagueGameslotAvailability'][] = array('league_id' => $league_id);
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
						// already selected. Fields and leagues are NOT selected, because those
						// are no longer in $this->data, but that's more of a feature than a bug.
					} else {
						$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('game slots', true)), 'default', array('class' => 'warning'));
					}
				}
			// Validate the input
			} else if (!array_key_exists('Field', $this->data)) {
				$this->Session->setFlash(__('You must select at least one field!', true), 'default', array('class' => 'info'));
			} else if (!array_key_exists('League', $this->data)) {
				$this->Session->setFlash(__('You must select at least one league!', true), 'default', array('class' => 'info'));
			} else {
				// By calling 'set', we deconstruct the dates from arrays to more useful strings
				$this->GameSlot->set ($this->data);
				$this->data = $this->GameSlot->data;
				$this->action = 'confirm';
			}
		}

		$id = $this->_arg('field');
		if ($id) {
			$this->GameSlot->Field->contain (array('ParentField'));
			$field = $this->GameSlot->Field->read(null, $id);
			$this->set(compact('field'));
		} else {
			$this->GameSlot->Field->contain (array(
					'Region',
					'ChildField' => array(
						'order' => 'ChildField.num',
						'fields' => 'ChildField.id, ChildField.name, ChildField.num',
						'conditions' => array(
							'ChildField.is_open' => true,
						),
					),
			));
			$fields = $this->GameSlot->Field->find('all', array(
					'order' => 'Region.id, Field.name',
					'fields' => 'Field.id, Field.name, Field.num, Region.id, Region.name',
					'conditions' => array(
						'Field.parent_id' => null,
						'Field.is_open' => true,
					),
			));
			$this->set(compact('fields'));
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
			$this->data['LeagueGameslotAvailability'] = array();
			foreach ($this->data['GameSlot']['league_id'] as $league_id) {
				$this->data['LeagueGameslotAvailability'][] = array(
					'game_slot_id' => $id,
					'league_id' => $league_id,
				);
			}

			// Wrap the whole thing in a transaction, for safety.
			$transaction = new DatabaseTransaction($this->GameSlot);

			if ($this->GameSlot->LeagueGameslotAvailability->deleteAll(array('game_slot_id' => $id))) {
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
					'LeagueGameslotAvailability',
			));
			$this->data = $this->GameSlot->read(null, $id);
		}
		$leagues = $this->GameSlot->Game->League->readByDate($this->data['GameSlot']['game_date']);
		$leagues = Set::combine($leagues, '{n}.League.id', '{n}.League.long_name');
		$this->data['GameSlot']['league_id'] = Set::extract ('/LeagueGameslotAvailability/league_id', $this->data);
		$this->set(compact('leagues'));
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
			if ($this->GameSlot->LeagueGameslotAvailability->deleteAll(array('game_slot_id' => $id))) {
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
