<?php
class GameSlotsController extends AppController {

	var $name = 'GameSlots';

	function isAuthorized() {
		if ($this->is_manager) {
			// Managers can perform these operations in affiliates they manage
			if (in_array ($this->params['action'], array(
					'add',
			)))
			{
				// If an affiliate id is specified, check if we're a manager of that affiliate
				$affiliate = $this->_arg('affiliate');
				if ($affiliate && in_array($affiliate, $this->UserCache->read('ManagedAffiliateIDs'))) {
					return true;
				}

				// If a field id is specified, check if we're a manager of that field
				$field = $this->_arg('field');
				if ($field) {
					if (in_array($this->GameSlot->Field->affiliate($field), $this->UserCache->read('ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}

			// Managers can perform these operations in affiliates they manage
			if (in_array ($this->params['action'], array(
					'edit',
					'view',
					'delete',
					'submit_score',
			)))
			{
				// If a game slot id is specified, check if we're a manager of that game slot's affiliate
				$slot = $this->_arg('slot');
				if ($slot) {
					if (in_array($this->GameSlot->affiliate($slot), $this->UserCache->read('ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}
		}

		return false;
	}

	function view() {
		$id = $this->_arg('slot');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game slot', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->GameSlot->contain(array(
				'Game' => array(
					'HomeTeam',
					'HomePoolTeam' => 'DependencyPool',
					'AwayTeam',
					'AwayPoolTeam' => 'DependencyPool',
					'Division' => 'League',
				),
				'Field' => array('Facility' => 'Region'),
				'DivisionGameslotAvailability' => array('Division' => 'League'),
		));
		$gameSlot = $this->GameSlot->read(null, $id);
		if (!$gameSlot) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game slot', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Configuration->loadAffiliate($gameSlot['Field']['Facility']['Region']['affiliate_id']);
		if (!empty($gameSlot['Game'])) {
			Configure::load("sport/{$gameSlot['Game'][0]['Division']['League']['sport']}");
		} else {
			Configure::load("sport/{$gameSlot['DivisionGameslotAvailability'][0]['Division']['League']['sport']}");
		}
		$this->set(compact('gameSlot'));
	}

	function add() {
		$field = $this->_arg('field');
		$affiliate = $this->_arg('affiliate');
		if (!$affiliate && !$field) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('affiliate', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		if ($field) {
			$this->GameSlot->Field->contain (array('Facility' => 'Region'));
			$field = $this->GameSlot->Field->read(null, $field);
			$affiliate = $field['Facility']['Region']['affiliate_id'];
			$this->set(compact('field'));
		} else {
			$regions = $this->GameSlot->Field->Facility->Region->find('all', array(
				'conditions' => array('Region.affiliate_id' => $affiliate),
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
		$this->Configuration->loadAffiliate($affiliate);
		$this->set(compact('affiliate'));

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
					// Use noon as the time, to avoid problems when we switch between DST and non-DST dates
					$date = strtotime ($this->data['GameSlot']['game_date'] . ' 12:00:00');
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

					$game_end = (empty ($this->data['GameSlot']['game_end']) ? null : $this->data['GameSlot']['game_end']);
					foreach ($this->data['GameSlot']['Create'] as $field_id => $field_dates) {
						foreach (array_keys ($field_dates) as $date) {
							$actual_game_end = (empty ($this->data['GameSlot']['game_end']) ? local_sunset_for_date($weeks[$date]) : $this->data['GameSlot']['game_end']);

							// Validate the end time
							if ($actual_game_end < $this->data['GameSlot']['game_start']) {
								$this->Session->setFlash(sprintf(__('Game end time of %s is before game start time of %s!', true), $actual_game_end, $this->data['GameSlot']['game_start']), 'default', array('class' => 'error'));
								return;
							}

							$overlap = $this->GameSlot->find('count', array(
									'contain' => array(),
									'conditions' => array(
										'field_id' => $field_id,
										'game_date' => $weeks[$date],
										'OR' => array(
											array(
												'game_start >' => $this->data['GameSlot']['game_start'],
												'game_start <' => $actual_game_end,
											),
											array(
												'game_start <' => $this->data['GameSlot']['game_start'],
												'game_end >' => $this->data['GameSlot']['game_start'],
											),
										),
									),
							));
							if ($overlap) {
								if (!isset($field)) {
									$this->GameSlot->Field->contain('Facility');
									$field = $this->GameSlot->Field->read(null, $field_id);
								}
								$name = "{$field['Facility']['name']} {$field['Field']['num']}";
								$this->Session->setFlash(sprintf(__('Detected a pre-existing conflict with the game slot to be created at %s on %s at %s. Unable to continue. (There may be more conflicts; this is only the first one detected.)', true), $this->data['GameSlot']['game_start'], $weeks[$date], $name), 'default', array('class' => 'error'));
								return;
							}

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

							// Try to save
							if (!$this->GameSlot->saveAll($slot)) {
								$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('game slots', true)), 'default', array('class' => 'warning'));
								return;
							}
						}
					}

					if ($transaction->commit() !== false) {
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

			if (!array_key_exists('Division', $this->data)) {
				$this->Session->setFlash(__('You must select at least one division!', true), 'default', array('class' => 'info'));
			} else {
				// The availability table isn't a standard HABTM, so we need to massage the
				// data into the correct form
				$this->data['DivisionGameslotAvailability'] = array();
				foreach ($this->data['Division'] as $division_id => $value) {
					if ($value) {
						$this->data['DivisionGameslotAvailability'][] = array(
							'game_slot_id' => $id,
							'division_id' => $division_id,
						);
					}
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
		}

		if (empty($this->data)) {
			$this->GameSlot->contain(array(
					'DivisionGameslotAvailability',
			));
			$this->data = $this->GameSlot->read(null, $id);
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game slot', true)), 'default', array('class' => 'info'));
				$this->redirect('/');
			}
		}
		$affiliate = $this->GameSlot->affiliate($id);
		$this->Configuration->loadAffiliate($affiliate);

		$divisions = $this->GameSlot->Game->Division->readByDate($this->data['GameSlot']['game_date'], $affiliate);
		$divisions = Set::combine($divisions, '{n}.Division.id', '{n}.Division.full_league_name');
		$this->set(compact('affiliate', 'divisions'));
	}

	function delete() {
		$id = $this->_arg('slot');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game slot', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$assigned = $this->GameSlot->field('assigned', compact('id'));
		if ($assigned) {
			$this->Session->setFlash(__('This game slot has a game assigned to it and cannot be deleted.', true), 'default', array('class' => 'warning'));
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

	function submit_score() {
		$id = $this->_arg('slot');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game slot', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->GameSlot->contain(array(
				'Game' => array(
					'HomeTeam',
					'HomePoolTeam' => 'DependencyPool',
					'Division' => 'League',
				),
				'Field' => array('Facility' => 'Region'),
		));
		$gameSlot = $this->GameSlot->read(null, $id);
		if (!$gameSlot) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game slot', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		if (empty($gameSlot['Game'])) {
			$this->Session->setFlash(__('This game slot has no games associated with it.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$game = $gameSlot['Game'][0];
		if ($game['Division']['schedule_type'] != 'competition') {
			$this->Session->setFlash(__('You can only enter scores for multiple games in "competition" divisions.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		if (Game::_is_finalized($game)) {
			$this->Session->setFlash(__('Games in this slot have already been finalized.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$end_time = strtotime("{$gameSlot['GameSlot']['game_date']} {$gameSlot['GameSlot']['display_game_end']}") +
				Configure::read('timezone.adjust') * 60;
		if ($end_time - 60 * 60 > time()) {
			$this->Session->setFlash(__('That game has not yet occurred!', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		// We need this in a couple of places
		if (League::hasSpirit($game['Division']['League'])) {
			$spirit_obj = $this->_getComponent ('Spirit', $game['Division']['League']['sotg_questions'], $this);
		}

		$this->Configuration->loadAffiliate($gameSlot['Field']['Facility']['Region']['affiliate_id']);
		Configure::load("sport/{$game['Division']['League']['sport']}");
		$ratings_obj = $this->_getComponent ('Ratings', $game['Division']['rating_calculator'], $this);

		$this->set(compact('gameSlot'));

		if (!empty ($this->data)) {
			$teams = $games = $incidents = $errors = array();

			$unplayed = in_array($this->data['Game']['status'], Configure::read('unplayed_status'));

			// We could put these as hidden fields in the form, but we'd need to
			// validate them against the values from the URL anyway, so it's
			// easier to just set them directly here.
			// We use the team_id as the array index, here and in the views,
			// because order matters, and this is a good way to ensure that
			// the correct data gets into the correct form.
			foreach ($gameSlot['Game'] as $i => $game) {
				if (!array_key_exists($game['home_team'], $this->data['Game'])) {
					$errors[$game['home_team']]['home_score'] = 'Scores must be entered for all teams.';
				} else {
					$details = $this->data['Game'][$game['home_team']];
					if ($unplayed) {
						$score = $rating = null;
					} else {
						$score = $details['home_score'];
						$rating = $ratings_obj->calculateRatingsChange($details['home_score']);
						$teams[$game['home_team']] = array(
								'id' => $game['home_team'],
								'rating' => $game['HomeTeam']['rating'] + $rating,
								// Any time that this is called, the division seeding might change.
								// We just reset it here, and it will be recalculated as required elsewhere.
								'seed' => 0,
						);
					}
					$games[$game['home_team']] = array(
							'id' => $game['id'],
							'status' => $this->data['Game']['status'],
							'home_score' => $score,
							'rating_points' => $rating,
							'approved_by' => $this->Auth->user('zuluru_person_id'),
					);
					if ($details['incident']) {
						$incidents[$game['home_team']] = array(
								'game_id' => $game['id'],
								'team_id' => $game['home_team'],
								'type' => $details['type'],
								'details' => $details['details'],
								'game' => $game,
						);
					}
				}
			}

			if (!empty($errors)) {
				$this->GameSlot->Game->validationErrors = $errors;
			} else {
				$transaction = new DatabaseTransaction($this->GameSlot);
				if ($this->GameSlot->Game->saveAll($games, array('validate' => 'first'))) {
					if (Configure::read('scoring.incident_reports') && !empty($incidents)) {
						if (!$this->GameSlot->Game->Incident->saveAll($incidents, array('validate' => 'first'))) {
							$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('incident data', true)), 'default', array('class' => 'warning'));
							return;
						}
					}

					// TODO: Replace this with a call to GamesController::_adjustScoreAndRatings, which will
					// need to be moved to a shared location and adjusted to handle competition differences.
					// For now, all that function does that this doesn't is stats, and we have no idea how
					// stats might play out in competition divisions, so this will suffice.
					$this->GameSlot->Game->Division->Team->saveAll($teams);

					$transaction->commit();

					foreach ($gameSlot['Game'] as $i => $game) {
						Cache::delete("division/{$game['Division']['id']}/standings", 'long_term');
						Cache::delete("division/{$game['Division']['id']}/schedule", 'long_term');
						Cache::delete('league/' . $this->GameSlot->Game->Division->league($game['Division']['id']) . '/standings', 'long_term');
						Cache::delete('league/' . $this->GameSlot->Game->Division->league($game['Division']['id']) . '/schedule', 'long_term');
					}

					$resultMessage = __('Scores have been saved and game results posted.', true);
					$resultClass = 'success';

					// TODO: Check for changes to the incident text to avoid sending a duplicate email,
					// and we probably want to change the text of the email slightly to let the recipient
					// know that it's an update instead of a new incident.
					// TODO: Combine code from here and games controller?
					$incidentMessage = '';
					if (Configure::read('scoring.incident_reports')) {
						$addr = Configure::read('email.incident_report_email');
						foreach ($incidents as $incident) {
							$this->set(array(
									'incident' => $incident,
									'game' => $incident['game'],
									'division' => $incident['game']['Division'],
									'slot' => $gameSlot['GameSlot'],
									'field' => $gameSlot['Field'],
									'home_team' => $incident['game']['HomeTeam'],
							));
							if ($this->_sendMail (array (
									'to' => "Incident Manager <$addr>",
									'from' => $this->UserCache->read('Person.email_formatted'),
									'subject' => "Incident report: {$incident['type']}",
									'template' => 'incident_report',
									'sendAs' => 'html',
							)))
							{
								// TODO: Maybe send the incident report before saving data, and add in a column for
								// whether it was sent, thus allowing the cron to attempt to re-send it?
								$incidentMessage = ' ' . __('Your incident report details have been sent for handling.', true);
							} else {
								App::import('Helper', 'Html');
								$html = new HtmlHelper();
								$link = $html->link($addr, "mailto:$addr");
								$incidentMessage = ' ' . sprintf(__('There was an error sending your incident report details. Please send them to %s to ensure proper handling.', true), $link);
								$resultClass = 'warning';
							}
						}
					}
					$resultMessage .= $incidentMessage;

					if ($resultMessage) {
						$this->Session->setFlash($resultMessage, 'default', array('class' => $resultClass));
					}

					$this->redirect('/');
				} else {
					$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('scores', true)), 'default', array('class' => 'warning'));
				}
			}
		}
	}
}
?>
