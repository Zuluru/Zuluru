<?php
class GamesController extends AppController {

	var $name = 'Games';
	var $helpers = array('ZuluruGame');
	var $components = array('Lock');

	// The PDFize plugin is optional, so we can't rely on it being present and put it in the components array.
	// But, because of how it functions, it does need to be initialized during the __construct phase.
	function __construct(){
		// But, at this time, the configuration hasn't been read from the database yet, so we can't use the
		// feature.pdfize setting to determine whether to include it. So, we check for existence of the file
		// and load it if it's there.
		if (file_exists(APP . 'plugins' . DS . 'pdfize' . DS . 'controllers' . DS . 'components' . DS . 'pdf.php')){
			$this->components['Pdfize.Pdf'] = array(
				'actions' => array(),
				'debug' => false,
				'size' => 'letter',
				'orientation' => 'portrait',
			);
		}
		parent::__construct();
	}

	function publicActions() {
		$actions = array('cron', 'view', 'tooltip', 'ical',
			// Attendance updates may come from emailed links; people might not be logged in
			'attendance_change',
		);
		if (Configure::read('feature.public')) {
			$actions[] = 'stats';
		}
		return $actions;
	}

	function isAuthorized() {
		// Anyone that's logged in can perform these operations
		if (in_array ($this->params['action'], array(
				'ratings_table',
				'note',
				'delete_note',
				'past',
				'future',
				'stats',
				'tweet',
		)))
		{
			return true;
		}

		// Volunteers can perform these operations any time
		// TODO: Restrict these based on task assignments?
		if (in_array ($this->params['action'], array(
				'live_score',
				'score_up',
				'score_down',
				'timeout',
				'play',
				'submit_score',
				'submit_stats',
		)))
		{
			return true;
		}

		// People can perform these operations on teams they are on
		if (in_array ($this->params['action'], array(
				'attendance',
				'live_score',
				'score_up',
				'score_down',
				'timeout',
				'play',
		)))
		{
			$team = $this->_arg('team');
			if ($team && in_array ($team, $this->Session->read('Zuluru.TeamIDs'))) {
				return true;
			}
		}

		// Captains are permitted to perform these operations for their teams
		if (in_array ($this->params['action'], array(
				'stat_sheet',
				'submit_score',
				'submit_stats',
		)))
		{
			// If a team id is specified, check if it belongs to the logged-in user
			$team = $this->_arg('team');
			if ($team && in_array ($team, $this->Session->read('Zuluru.OwnedTeamIDs'))) {
				return true;
			}
		}

		// Permit coordinators to perform these operations on their games
		if (in_array ($this->params['action'], array(
				'edit',
				'edit_boxscore',
				'delete_score',
				'add_score',
				'delete',
				'stat_sheet',
				'submit_stats',
		)))
		{
			$game = $this->_arg('game');
			if ($game) {
				$divisions = $this->Session->read('Zuluru.DivisionIDs');
				if (!empty ($divisions)) {
					$coord = $this->Game->find ('count', array(
							'conditions' => array(
								'Game.id'			=> $game,
								'Game.division_id'	=> $divisions,
							)
					));
					if ($coord > 0) {
						return true;
					}
				}
			}
		}

		return false;
	}

	function view() {
		$id = $this->_arg('game');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$contain = array (
			'Division' => array('League'),
			'GameSlot' => array('Field' => 'Facility'),
			// Get the list of captains for each team, we may need to email them
			'HomeTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.role' => Configure::read('privileged_roster_roles')),
					'fields' => array('id', 'first_name', 'last_name', 'email'),
				),
			),
			'HomePoolTeam' => 'DependencyPool',
			'AwayTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.role' => Configure::read('privileged_roster_roles')),
					'fields' => array('id', 'first_name', 'last_name', 'email'),
				),
			),
			'AwayPoolTeam' => 'DependencyPool',
			'ApprovedBy',
			'ScoreEntry' => array('Person'),
			'ScoreDetail' => array(
				'order' => array('ScoreDetail.created', 'ScoreDetail.id'),
				'ScoreDetailStat' => array('Person', 'StatType'),
			),
			'SpiritEntry',
			'Allstar' => array('Person'),
			'Incident',
		);
		if (Configure::read('feature.annotations') && $this->is_logged_in) {
			$contain['Note'] = array(
				'CreatedPerson',
				'conditions' => array(
					'Note.created_team_id' => $this->Session->read('Zuluru.TeamIDs'),
					'OR' => array(
						'Note.visibility' => VISIBILITY_TEAM,
						array('AND' => array(
							'Note.visibility' => VISIBILITY_CAPTAINS,
							'Note.created_team_id' => $this->Session->read('Zuluru.OwnedTeamIDs'),
						)),
						array('AND' => array(
							'Note.visibility' => VISIBILITY_PRIVATE,
							'Note.created_person_id' => $this->Auth->user('id'),
						)),
					),
				),
			);
		}
		$this->Game->contain ($contain);
		$game = $this->Game->read(null, $id);
		if (!$game) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Configuration->loadAffiliate($game['Division']['League']['affiliate_id']);
		Configure::load("sport/{$game['Division']['League']['sport']}");
		$this->Game->_adjustEntryIndices($game);
		$this->Game->_readDependencies($game);

		$this->set('game', $game);
		$this->set('spirit_obj', $this->_getComponent ('Spirit', $this->Game->data['Division']['League']['sotg_questions'], $this));
		$this->set('league_obj', $this->_getComponent ('LeagueType', $this->Game->data['Division']['schedule_type'], $this));
		$this->set('ratings_obj', $this->_getComponent ('Ratings', $this->Game->data['Division']['rating_calculator'], $this));
		$this->set('is_coordinator', in_array ($this->Game->data['Division']['id'], $this->Session->read('Zuluru.DivisionIDs')));
	}

	function tooltip() {
		$id = $this->_arg('game');
		if (!$id) {
			return;
		}
		$this->Game->contain(array(
			'HomeTeam',
			'AwayTeam',
			'GameSlot' => array('Field' => array('Facility' => 'Region')),
		));

		$game = $this->Game->read(null, $id);
		if (!$game) {
			return;
		}
		$this->Configuration->loadAffiliate($game['GameSlot']['Field']['Facility']['Region']['affiliate_id']);
		$this->set(compact('game'));

		Configure::write ('debug', 0);
		$this->layout = 'ajax';
	}

	function ratings_table() {
		$id = $this->_arg('game');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		if (!empty ($this->data)) {
			$this->set('rating_home', $this->data['Game']['rating_home']);
			$this->set('rating_away', $this->data['Game']['rating_away']);
		}

		$this->Game->contain (array (
			'Division' => array('League'),
			'HomeTeam',
			'AwayTeam',
		));
		$game = $this->Game->read(null, $id);
		if (!$game) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Configuration->loadAffiliate($game['Division']['League']['affiliate_id']);
		$ratings_obj = $this->_getComponent ('Ratings', $this->Game->data['Division']['rating_calculator'], $this);
		$max_score = $this->Game->data['Division']['League']['expected_max_score'];
		$this->set(compact('game', 'ratings_obj', 'max_score'));
	}

	// This function takes the parameters the old-fashioned way, to try to be more third-party friendly
	function ical($game_id, $team_id) {
		$this->layout = 'ical';
		if (!$game_id || !$team_id) {
			return;
		}

		$this->Game->contain (array (
			'HomeTeam',
			'AwayTeam',
			'GameSlot' => array('Field' => array('Facility' => 'Region')),
		));
		$game = $this->Game->read(null, $game_id);
		if (!$game || !$game['Game']['published'] ||
			($team_id != $game['Game']['home_team'] && $team_id != $game['Game']['away_team']))
		{
			return;
		}
		$this->Configuration->loadAffiliate($game['GameSlot']['Field']['Facility']['Region']['affiliate_id']);

		$this->set ('calendar_type', 'Game');
		$this->set ('calendar_name', 'Game');
		$this->set(compact ('game', 'team_id'));

		Configure::write ('debug', 0);
	}

	function edit() {
		$id = $this->_arg('game');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		// We need some basic game information right off. Much of the
		// data we display here doesn't come from the form, so we have
		// to read the whole thing.
		$this->Game->contain (array (
			'Division' => array(
				'Person' => array('fields' => array('id', 'first_name', 'last_name', 'email')),
				'League',
			),
			'GameSlot' => array('Field' => 'Facility'),
			'HomeTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.role' => Configure::read('extended_playing_roster_roles')),
					'fields' => array('id', 'first_name', 'last_name', 'gender', 'email'),
				),
			),
			'HomePoolTeam' => 'DependencyPool',
			'AwayTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.role' => Configure::read('extended_playing_roster_roles')),
					'fields' => array('id', 'first_name', 'last_name', 'gender', 'email'),
				),
			),
			'AwayPoolTeam' => 'DependencyPool',
			'ApprovedBy',
			'ScoreEntry' => array('Person'),
			'SpiritEntry',
			'Allstar' => array('Person'),
			'Incident',
		));
		$game = $this->Game->read(null, $id);
		if (!$game) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Configuration->loadAffiliate($game['Division']['League']['affiliate_id']);
		$this->Game->_adjustEntryIndices($game);
		$this->Game->_readDependencies($game);

		if (!$this->is_admin && !in_array ($game['Division']['id'], $this->Session->read('Zuluru.DivisionIDs'))) {
			$this->Session->setFlash(__('You do not have permission to edit that game.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$this->Game->contain(array(
			// Get the list of captains for each team, for the email link
			'HomeTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.role' => Configure::read('privileged_roster_roles')),
					'fields' => array('id', 'first_name', 'last_name', 'email'),
				),
			),
			'AwayTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.role' => Configure::read('privileged_roster_roles')),
					'fields' => array('id', 'first_name', 'last_name', 'email'),
				),
			),
		));
		$captains = $this->Game->read(null, $id);

		// Spirit score entry validation comes from the spirit component
		$spirit_obj = $this->_getComponent ('Spirit', $game['Division']['League']['sotg_questions'], $this);
		$league_obj = $this->_getComponent ('LeagueType', $game['Division']['schedule_type'], $this);
		$this->Game->SpiritEntry->validate = $spirit_obj->getValidate($game['Division']['League']);

		if (!empty($this->data)) {
			// We could put these as hidden fields in the form, but we'd need to
			// validate them against the values from the URL anyway, so it's
			// easier to just set them directly here.
			// We use the team_id as the array index, here and in the views,
			// because order matters, and this is a good way to ensure that
			// the correct data gets into the correct form.
			// PLAYOFF TODO: Method of handling the various dependencies
			$this->data['Game']['id'] = $id;
			$this->data['Game']['approved_by'] = $this->Auth->user('id');
			$this->data['SpiritEntry'][$game['Game']['home_team']]['team_id'] = $game['Game']['home_team'];
			$this->data['SpiritEntry'][$game['Game']['home_team']]['created_team_id'] = $game['Game']['away_team'];
			if (array_key_exists($game['Game']['home_team'], $game['SpiritEntry'])) {
				$this->data['SpiritEntry'][$game['Game']['home_team']]['id'] = $game['SpiritEntry'][$game['Game']['home_team']]['id'];
			}
			$this->data['SpiritEntry'][$game['Game']['away_team']]['team_id'] = $game['Game']['away_team'];
			$this->data['SpiritEntry'][$game['Game']['away_team']]['created_team_id'] = $game['Game']['home_team'];
			if (array_key_exists($game['Game']['away_team'], $game['SpiritEntry'])) {
				$this->data['SpiritEntry'][$game['Game']['away_team']]['id'] = $game['SpiritEntry'][$game['Game']['away_team']]['id'];
			}

			// We need to merge the two allstar nomination areas
			$allstars = array();
			if (array_key_exists ('Allstar', $this->data)) {
				foreach ($this->data['Allstar'] as $team_allstars) {
					if (is_array($team_allstars['person_id'])) {
						foreach ($team_allstars['person_id'] as $allstar) {
							$allstars[] = array('person_id' => $allstar);
						}
					}
				}
				if (empty ($allstars)) {
					unset ($this->data['Allstar']);
				} else {
					$this->data['Allstar'] = $allstars;
				}
			}

			// Wrap the whole thing in a transaction, for safety.
			$transaction = new DatabaseTransaction($this->Game);

			$this->_adjustScoreAndRatings($game, $this->data);

			if ($this->Game->Allstar->deleteAll(array('game_id' => $id))) {
				if ($this->Game->saveAll($this->data, array('validate' => 'first'))) {
					$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('game', true)), 'default', array('class' => 'success'));
					$this->_updateDependencies ($game, $this->data['Game']['home_score'] > $this->data['Game']['away_score']);

					// Delete score entries
					$this->Game->ScoreEntry->deleteAll(array('game_id' => $id));
					$transaction->commit();

					$cache_file = CACHE . 'queries' . DS . "division_{$game['Division']['id']}.data";
					if (file_exists($cache_file)) {
						unlink($cache_file);
					}
					$cache_file = CACHE . 'queries' . DS . "schedule_{$game['Division']['id']}.data";
					if (file_exists($cache_file)) {
						unlink($cache_file);
					}

					if ($this->_arg('stats')) {
						$this->redirect(array('action' => 'submit_stats', 'game' => $id));
					} else {
						$this->redirect(array('action' => 'view', 'game' => $id));
					}
				} else {
					$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('game', true)), 'default', array('class' => 'warning'));
				}
			}
		}

		if (empty($this->data)) {
			$this->data = $game;
		}

		// To maximize shared code between the edit and view templates, we'll
		// set it in the 'game' variable here too.
		$this->set(compact (array ('game', 'captains', 'spirit_obj', 'league_obj')));
		$this->set('is_coordinator', in_array ($game['Division']['id'], $this->Session->read('Zuluru.DivisionIDs')));
	}

	function edit_boxscore() {
		$id = $this->_arg('game');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$this->Game->contain (array (
			'Division' => array(
				'League' => array(
					'StatType' => array('conditions' => array('StatType.type' => 'entered')),
				),
			),
			'GameSlot' => array('Field' => 'Facility'),
			'HomeTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.role' => Configure::read('extended_playing_roster_roles')),
					'fields' => array('id', 'first_name', 'last_name', 'gender'),
				),
			),
			'HomePoolTeam' => 'DependencyPool',
			'AwayTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.role' => Configure::read('extended_playing_roster_roles')),
					'fields' => array('id', 'first_name', 'last_name', 'gender'),
				),
			),
			'AwayPoolTeam' => 'DependencyPool',
			'ScoreDetail' => array(
				'order' => array('ScoreDetail.created', 'ScoreDetail.id'),
				'ScoreDetailStat' => array('Person', 'StatType'),
			),
		));
		$game = $this->Game->read(null, $id);
		if (!$game) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Configuration->loadAffiliate($game['Division']['League']['affiliate_id']);
		Configure::load("sport/{$game['Division']['League']['sport']}");
		$this->Game->_readDependencies($game);
		$this->set(compact('game'));

		if (!empty($this->data)) {
			// saveAll handles hasMany relations OR multiple records, but not both,
			// so we have to save each pool separately. Wrap the whole thing in a
			// transaction, for safety.
			$transaction = new DatabaseTransaction($this->Game->ScoreDetail);
			$success = true;
			foreach ($this->data['ScoreDetail'] as $detail) {
				if (!empty($detail['ScoreDetailStat'])) {
					foreach ($detail['ScoreDetailStat'] as $key => $stat) {
						if (empty($stat['person_id'])) {
							unset($detail['ScoreDetailStat'][$key]);
						}
					}
				}
				if (empty($detail['ScoreDetailStat'])) {
					unset($detail['ScoreDetailStat']);
				}
				if (!empty($detail['ScoreDetail']['id'])) {
					$success &= $this->Game->ScoreDetail->ScoreDetailStat->deleteAll(array('score_detail_id' => $detail['ScoreDetail']['id']));
				}
				$success &= $this->Game->ScoreDetail->saveAll($detail, array('atomic' => false));
				if (!$success) {
					pr($this->Game->ScoreDetail->validationErrors);
				}
			}
			if ($success) {
				$this->Session->setFlash(sprintf(__('The %s have been saved', true), __('score details', true)), 'default', array('class' => 'success'));
				$transaction->commit();
				$this->redirect(array('action' => 'view', 'game' => $id));
			} else {
				$this->Session->setFlash(__('Failed to save the score details!', true), 'default', array('class' => 'warning'));
			}
		}
	}

	function delete_score() {
		$id = $this->_arg('detail');
		$game_id = $this->Game->ScoreDetail->field('game_id', array('id' => $id));
		if ($game_id != $this->_arg('game')) {
			$this->set('error', __('Invalid score detail id', true));
		} else {
			if ($this->Game->ScoreDetail->delete($id)) {
				$this->set(compact('id'));
			} else {
				$this->set('error', sprintf (__('Failed to delete %s', true), __('score detail', true)));
			}
		}
		if (!$this->RequestHandler->isAjax()) {
			$this->redirect(array('action' => 'edit_boxscore', 'game' => $game_id));
		}
	}

	function add_score() {
		$game_id = $this->_arg('game');
		$this->Game->contain (array (
			'Division' => array(
				'League' => array(
					'StatType' => array('conditions' => array('StatType.type' => 'entered')),
				),
			),
			'HomeTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.role' => Configure::read('extended_playing_roster_roles')),
					'fields' => array('id', 'first_name', 'last_name', 'gender'),
				),
			),
			'AwayTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.role' => Configure::read('extended_playing_roster_roles')),
					'fields' => array('id', 'first_name', 'last_name', 'gender'),
				),
			),
		));
		$game = $this->Game->read(null, $game_id);
		if (!$game) {
			if (!$this->RequestHandler->isAjax()) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game', true)), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'edit_boxscore', 'game' => $game_id));
			}
			$this->set('error', sprintf(__('Invalid %s', true), __('game', true)));
			return;
		}

		$this->Configuration->loadAffiliate($game['Division']['League']['affiliate_id']);
		Configure::load("sport/{$game['Division']['League']['sport']}");

		$detail = array_merge($this->data['AddDetail'], array(
				'game_id' => $game_id
		));
		$saved = $this->Game->ScoreDetail->save($detail);
		if ($saved) {
			$saved['ScoreDetail']['id'] = $this->Game->ScoreDetail->id;
			$this->set(compact('game', 'saved'));
		} else if ($this->RequestHandler->isAjax()) {
			$this->set('error', array_shift($this->Game->ScoreDetail->validationErrors));
			return;
		} else {
			$this->render('edit_boxscore');
		}
	}

	function note() {
		$game_id = $this->_arg('game');
		$note_id = $this->_arg('note');
		$my_id = $this->Auth->user('id');

		if (!$game_id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Game->contain(array(
				'Division' => 'League',
				'HomeTeam',
				'AwayTeam',
				'GameSlot' => array('Field' => 'Facility'),
		));
		$game = $this->Game->read(null, $game_id);
		if (!$game) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Configuration->loadAffiliate($game['Division']['League']['affiliate_id']);
		$this->set(compact('game'));

		// Make sure that this person is playing in this game
		$my_teams = $this->Session->read('Zuluru.TeamIDs');
		if (!in_array($game['Game']['home_team'], $my_teams) && !in_array($game['Game']['away_team'], $my_teams)) {
			$this->Session->setFlash(__('You are not on the roster of a team playing in this game.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'game' => $game_id));
		}

		if (!empty($this->data)) {
			// Check that this user is allowed to edit this note
			if (!empty($this->data['Note']['id'])) {
				$created = $this->Game->Note->field('created_person_id', array('id' => $this->data['Note']['id']));
				if ($created != $my_id) {
					$this->Session->setFlash(sprintf(__('You are not allowed to edit that %s.', true), __('note', true)), 'default', array('class' => 'error'));
					$this->redirect(array('action' => 'view', 'game' => $game_id));
				}
			}

			$this->data['Note']['game_id'] = $game_id;
			if (in_array($game['Game']['home_team'], $my_teams)) {
				$this->data['Note']['created_team_id'] = $game['Game']['home_team'];
				$opponent = $game['AwayTeam'];
			} else {
				$this->data['Note']['created_team_id'] = $game['Game']['away_team'];
				$opponent = $game['HomeTeam'];
			}

			if (empty($this->data['Note']['note'])) {
				if (!empty($this->data['Note']['id'])) {
					if ($this->Game->Note->delete($this->data['Note']['id'])) {
						$this->Session->setFlash(sprintf(__('The %s has been deleted', true), __('note', true)), 'default', array('class' => 'success'));
					} else {
						$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Note', true)), 'default', array('class' => 'warning'));
					}
				} else {
					$this->Session->setFlash(__('You entered no text, so no note was added.', true), 'default', array('class' => 'warning'));
				}
				$this->redirect(array('action' => 'view', 'game' => $game_id));
			} else if ($this->Game->Note->save($this->data['Note'])) {
				// Send an email on new notes
				if (empty($this->data['Note']['id'])) {
					switch ($this->data['Note']['visibility']) {
						case VISIBILITY_CAPTAINS:
							$roles = Configure::read('privileged_roster_roles');
							break;
						case VISIBILITY_TEAM:
							$roles = Configure::read('regular_roster_roles');
							break;
					}
					if (isset($roles)) {
						$this->Game->Division->Team->contain(array(
							'Person' => array('conditions' => array(
									'TeamsPerson.role' => $roles,
									'Person.id !=' => $my_id,
							)),
						));
						$team = $this->Game->Division->Team->read(null, $this->data['Note']['created_team_id']);
						if (!empty($team['Person'])) {
							$person = $this->Session->read('Zuluru.Person');
							$this->set(compact('person', 'team', 'opponent'));
							$this->_sendMail (array (
									'to' => $team['Person'],
									'replyTo' => $person,
									'subject' => "{$team['Team']['name']} game note",
									'template' => 'game_note',
									// Notes are entered as HTML
									'sendAs' => 'html',
							));
						}
					}
				}

				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('note', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'view', 'game' => $game_id));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('note', true)), 'default', array('class' => 'warning'));
			}
		}
		if (empty($this->data)) {
			if ($note_id) {
				$this->Game->Note->contain();
				$this->data = $this->Game->Note->read(null, $note_id);
				if (!$this->data) {
					$this->Session->setFlash(sprintf(__('Invalid %s', true), __('note', true)), 'default', array('class' => 'info'));
					$this->redirect(array('action' => 'view', 'game' => $game_id));
				}
			} else {
				$this->data = array('Note' => compact('game_id'));
			}
		}

		if (Configure::read('feature.tiny_mce')) {
			$this->helpers[] = 'TinyMce.TinyMce';
		}
	}

	function delete_note() {
		$note_id = $this->_arg('note');
		$my_id = $this->Auth->user('id');

		if (!$note_id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('note', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Game->Note->contain();
		$note = $this->Game->Note->read(null, $note_id);
		if (!$note) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('note', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		if ($note['Note']['created_person_id'] != $my_id) {
			$this->Session->setFlash(__('You can only delete notes that you created.', true), 'default', array('class' => 'warning'));
		} else if ($this->Person->Note->delete($note_id)) {
			$this->Session->setFlash(sprintf(__('The %s has been deleted', true), __('note', true)), 'default', array('class' => 'success'));
		} else {
			$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Note', true)), 'default', array('class' => 'warning'));
		}
		$this->redirect(array('action' => 'view', 'game' => $note['Note']['game_id']));
	}

	function delete() {
		$id = $this->_arg('game');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$this->Game->contain (array (
			'Division' => array('League', 'Person' => array('fields' => array('id', 'first_name', 'last_name', 'email'))),
			'GameSlot' => array('Field' => 'Facility'),
			'HomeTeam',
			'AwayTeam',
			'ApprovedBy',
			'ScoreEntry' => array('Person'),
			'SpiritEntry',
			'Allstar' => array('Person'),
			'Incident',
		));
		$game = $this->Game->read(null, $id);
		if (!$game) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Configuration->loadAffiliate($game['Division']['League']['affiliate_id']);

		$msg = null;
		if (!$this->_arg('force')) {
			if (Game::_is_finalized($game)) {
				$msg = 'The score for this game has already been finalized.';
			}
			if (!empty ($game['ScoreEntry'])) {
				$msg = 'A score has already been submitted for this game.';
			}
		}

		if ($msg) {
			App::import('Helper', 'Html');
			$html = new HtmlHelper();
			$this->Session->setFlash(__($msg, true) . ' ' .
					sprintf(__('If you are absolutely sure that you want to delete it anyway, %s. <b>This cannot be undone!</b>', true), $html->link(__('click here', true), array('action' => 'delete', 'game' => $id, 'force' => true))),
					'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'game' => $id));
		}

		// If the game isn't finalized, and there's no score entry, then there won't
		// be any other related records either, and it's safe to delete it.
		// Wrap the whole thing in a transaction, for safety.
		$transaction = new DatabaseTransaction($this->Game);
		if ($this->Game->delete($id)) {
			if ($this->Game->GameSlot->updateAll (array('game_id' => null), array('GameSlot.id' => $game['GameSlot']['id']))) {
				$this->Session->setFlash(sprintf(__('%s deleted', true), __('Game', true)), 'default', array('class' => 'success'));

				// If we already have a rating, reverse the effect of this game from the
				// team ratings
				if (!is_null($game['Game']['rating_points']) && $game['Game']['rating_points'] != 0) {
					if ($game['Game']['home_score'] >= $game['Game']['away_score']) {
						$data = array(
							array(
								'id' => $game['HomeTeam']['id'],
								'rating' => $game['HomeTeam']['rating'] - $game['Game']['rating_points'],
							),
							array(
								'id' => $game['AwayTeam']['id'],
								'rating' => $game['AwayTeam']['rating'] + $game['Game']['rating_points'],
							),
						);
					} else if($game['Game']['away_score'] > $game['Game']['home_score']) {
						$data = array(
							array(
								'id' => $game['HomeTeam']['id'],
								'rating' => $game['HomeTeam']['rating'] + $game['Game']['rating_points'],
							),
							array(
								'id' => $game['AwayTeam']['id'],
								'rating' => $game['AwayTeam']['rating'] - $game['Game']['rating_points'],
							),
						);
					}
					if (!$this->Game->HomeTeam->saveAll ($data)) {
						$this->Session->setFlash(__('Game was deleted, but ratings were not reset', true), 'default', array('class' => 'warning'));
					}
				}

				$transaction->commit();

				$cache_file = CACHE . 'queries' . DS . "division_{$game['Division']['id']}.data";
				if (file_exists($cache_file)) {
					unlink($cache_file);
				}
				$cache_file = CACHE . 'queries' . DS . "schedule_{$game['Division']['id']}.data";
				if (file_exists($cache_file)) {
					unlink($cache_file);
				}

				$this->redirect(array('controller' => 'divisions', 'action' => 'schedule', 'division' => $game['Division']['id']));
			} else {
				$this->Session->setFlash(__('Game was deleted, but game slot was not cleared', true), 'default', array('class' => 'warning'));
			}
		} else {
			$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Game', true)), 'default', array('class' => 'warning'));
		}
		$this->redirect(array('controller' => 'divisions', 'action' => 'schedule', 'division' => $game['Division']['id']));
	}

	function attendance() {
		$id = $this->_arg('game');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$team_id = $this->_arg('team');
		if (!$team_id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$this->Game->contain(array(
			'Division' => array('Day', 'League'),
			'HomeTeam',
			'AwayTeam',
			'GameSlot' => array('Field' => 'Facility'),
		));
		$game = $this->Game->read(null, $id);
		if (!$game) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Configuration->loadAffiliate($game['Division']['League']['affiliate_id']);
		if ($game['Game']['home_team'] == $team_id) {
			$team = $game['HomeTeam'];
			$opponent = $game['AwayTeam'];
		} else if ($game['Game']['away_team'] == $team_id) {
			$team = $game['AwayTeam'];
			$opponent = $game['HomeTeam'];
		} else {
			$this->Session->setFlash(__('That team is not playing in this game.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		if (!$team['track_attendance']) {
			$this->Session->setFlash(__('That team does not have attendance tracking enabled.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$attendance = $this->Game->_read_attendance($team_id, Set::extract('/Division/Day/id', $game), $id);
		$this->set(compact('game', 'team', 'opponent', 'attendance'));
		$this->set('is_captain', in_array($team_id, $this->Session->read('Zuluru.OwnedTeamIDs')));
	}

	function add_sub() {
	}

	function attendance_change() {
		$id = $this->_arg('game');
		$date = $this->_arg('date');
		if (!$id && !$date) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$team_id = $this->_arg('team');
		if (!$team_id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$person_id = $this->_arg('person');
		$my_id = $this->Auth->user('id');
		if (!$person_id) {
			$person_id = $my_id;
			if (!$person_id) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('player', true)), 'default', array('class' => 'info'));
				$this->redirect('/');
			}
		}

		if ($id) {
			$this->Game->contain(array(
				// Get the list of captains for each team, we may need to email them
				'HomeTeam' => array(
					'Person' => array(
						'conditions' => array('TeamsPerson.role' => Configure::read('privileged_roster_roles')),
						'fields' => array('id', 'first_name', 'last_name', 'email'),
					),
				),
				'AwayTeam' => array(
					'Person' => array(
						'conditions' => array('TeamsPerson.role' => Configure::read('privileged_roster_roles')),
						'fields' => array('id', 'first_name', 'last_name', 'email'),
					),
				),
				'GameSlot' => array('Field' => array('Facility' => 'Region')),
				// We need to specify the team id here, in case the person is on both teams in this game
				'Attendance' => array(
					'conditions' => array(
						'team_id' => $team_id,
						'person_id' => $person_id,
					),
					'Person' => array(
						'Team' => array(
							'conditions' => array('team_id' => $team_id),
						),
					),
				),
			));
			$game = $this->Game->read(null, $id);
			if (!$game) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game', true)), 'default', array('class' => 'info'));
				$this->redirect('/');
			}
			$this->Configuration->loadAffiliate($game['GameSlot']['Field']['Facility']['Region']['affiliate_id']);
			$date = $game['GameSlot']['game_date'];
			$past = (strtotime("{$game['GameSlot']['game_date']} {$game['GameSlot']['game_start']}") + Configure::read('timezone.adjust') * 60 < time());

			if ($game['Game']['home_team'] == $team_id) {
				$team = $game['HomeTeam'];
				$opponent = $game['AwayTeam'];
			} else if ($game['Game']['away_team'] == $team_id) {
				$team = $game['AwayTeam'];
				$opponent = $game['HomeTeam'];
			} else {
				$this->Session->setFlash(__('That team is not playing in this game.', true), 'default', array('class' => 'info'));
				$this->redirect('/');
			}

			// Pull out the player and attendance records.
			$attendance = $game['Attendance'][0];
			$person = $attendance['Person'];
		} else {
			$this->Game->Attendance->contain(array(
				'Person' => array(
					'Team' => array(
						'conditions' => array('team_id' => $team_id),
					),
				),
				'Team',
			));
			$record = $this->Game->Attendance->find('first', array(
					'conditions' => array(
						'person_id' => $person_id,
						'team_id' => $team_id,
						'game_date' => $date,
					),
			));

			// Pull out the player, attendance and team records.
			$attendance = $record['Attendance'];
			$person = $record['Person'];
			$team = $record['Team'];
			$past = false;
		}

		if (!$team['track_attendance']) {
			$this->Session->setFlash(__('That team does not have attendance tracking enabled.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		if (!$attendance) {
			$this->Session->setFlash(__('That person does not have an attendance record for this game.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$is_me = ($person_id == $this->Auth->user('id'));
		$is_captain = in_array ($team_id, $this->Session->read('Zuluru.OwnedTeamIDs'));
		$is_coordinator = in_array ($team['division_id'], $this->Session->read('Zuluru.DivisionIDs'));

		// We must do other permission checks here, because we allow non-logged-in users to accept
		// through email links
		$code = $this->_arg('code');
		if ($code) {
			// Authenticate the hash code
			$player_hash = $this->_hash($attendance);
			$captain_hash = $this->_hash(array_merge ($attendance, array('captain' => true)));
			// Temporary addition during hash conversion period
			$player_hash2 = $this->_hash($attendance, false);
			$captain_hash2 = $this->_hash(array_merge ($attendance, array('captain' => true)), false);
			if ($player_hash == $code || $player_hash2 == $code) {
				// Only the player will have this confirmation code
				$is_me = true;
			} else if ($captain_hash == $code || $captain_hash2 == $code) {
				$is_captain = true;
			} else {
				$this->Session->setFlash(__('The authorization code is invalid.', true), 'default', array('class' => 'warning'));
				$this->redirect('/');
			}

			// Fake the posted data array with the status from the URL
			$this->data = array('Person' => array('status' => $this->_arg('status')));
		} else {
			// Players can change their own attendance, captains and coordinators can change any attendance on their teams
			if (!$is_me && !$is_captain && !$is_coordinator) {
				$this->Session->setFlash(__('You are not allowed to change this attendance record.', true), 'default', array('class' => 'info'));
				$this->redirect('/');
			}
		}

		$role = $person['Team'][0]['TeamsPerson']['role'];
		$attendance_options = $this->Game->_attendanceOptions ($team_id, $role, $attendance['status'], $past, $is_captain);
		$this->set(compact('game', 'date', 'team', 'opponent', 'person', 'status', 'attendance', 'attendance_options', 'is_captain', 'is_me'));

		if (!empty ($this->data)) {
			$this->Game->Attendance->id = $attendance['id'];

			// This "days" calculation isn't precise, as it doesn't handle leap years.
			// However, it's close enough since we're never looking at periods that span
			// from a year end to a leap day.
			$days = date('Y') * 365 + date('z');
			$days_to_game = date('Y', strtotime($date)) * 365 + date('z', strtotime($date)) - $days;

			if (array_key_exists('status', $this->data['Person'])) {
				$this->set('status', $this->data['Person']['status']);
				$this->set('comment', $attendance['comment']);
				$result = $this->_updateAttendanceStatus($team, $person, $date, $is_captain, $is_me, $attendance, $days_to_game, $past, $attendance_options);
			} else {
				$this->set('status', $attendance['status']);
				$this->set('comment', $this->data['Person']['comment']);
				$result = $this->_updateAttendanceComment($team, $person, $date, $is_captain, $is_me, $attendance, $days_to_game, $past);
			}

			// Where do we go from here? It depends...
			if (!$result) {
				if ($code) {
					$this->redirect('/');
				}
			} else {
				if ($this->RequestHandler->isAjax()) {
					$this->action = 'attendance_change_ajax';
					$this->set('dedicated', $this->data['dedicated']);
				} else if (!$this->is_logged_in) {
					$this->redirect(array('controller' => 'teams', 'action' => 'view', 'team' => $team_id));
				} else if ($id) {
					$this->redirect(array('action' => 'attendance', 'team' => $team_id, 'game' => $id));
				} else {
					$this->redirect(array('controller' => 'teams', 'action' => 'attendance', 'team' => $team_id));
				}
			}
		}
	}

	function _updateAttendanceStatus($team, $person, $date, $is_captain, $is_me, $attendance, $days_to_game, $past, $attendance_options) {
		$status = $this->data['Person']['status'];
		if (!array_key_exists ($status, $attendance_options)) {
			$this->Session->setFlash(__('That is not currently a valid attendance status for this player for this game.', true), 'default', array('class' => 'info'));
			return false;
		}

		if ($status == $attendance['status'] &&
			// Non-JavaScript submissions might include a comment
			(!array_key_exists('comment', $this->data['Person']) || empty($this->data['Person']['comment'])) &&
			// Invitations might include a note from the captain
			(!array_key_exists('note', $this->data['Person']) || empty($this->data['Person']['note'])))
		{
			return true;
		}

		if (!$this->Game->Attendance->saveField ('status', $status)) {
			$this->Session->setFlash(__('Failed to update the attendance status!', true), 'default', array('class' => 'warning'));
			return false;
		}
		if (array_key_exists('comment', $this->data['Person'])) {
			$comment = $this->data['Person']['comment'];
			if ($comment != $attendance['comment']) {
				if (!$this->Game->Attendance->saveField ('comment', $comment)) {
					$this->Session->setFlash(__('Failed to update the attendance comment!', true), 'default', array('class' => 'warning'));
					return false;
				}
			}
		}

		if (!$this->RequestHandler->isAjax()) {
			$this->Session->setFlash(sprintf (__('Attendance has been updated to %s.', true), $attendance_options[$status]), 'default', array('class' => 'success'));
		}

		// Maybe send some emails, only if the game is in the future
		if (!$past) {
			$role = $person['Team'][0]['TeamsPerson']['role'];

			// Send email from the player to the captain if it's within the configured date range
			if ($is_me && $team['attendance_notification'] >= $days_to_game) {
				// Make sure the current player isn't in the list of captains to send to
				$captains = Set::extract ("/Person[id!={$person['id']}]", $team);
				if (!empty ($captains)) {
					if (array_key_exists('comment', $this->data['Person']) && !empty($this->data['Person']['comment'])) {
						$this->set('comment', $this->data['Person']['comment']);
					}

					$this->set('captains', implode (', ', Set::extract ('/Person/first_name', $captains)));
					$this->set('code', $this->_hash(array_merge ($attendance, array('captain' => true))));
					$this->_sendMail (array (
							'to' => $captains,
							'replyTo' => $person,
							'subject' => "{$team['name']} attendance change",
							'template' => 'attendance_captain_notification',
							'sendAs' => 'both',
					));
				}
			}
			// Always send an email from the captain to substitute players. It will likely
			// be an invitation to play or a response to a request or cancelling attendance
			// if another player is available. Regardless, we need to communicate this.
			else if ($is_captain && !in_array($role, Configure::read('playing_roster_roles'))) {
				$captain = $this->Session->read('Zuluru.Person.full_name');
				if (!$captain) {
					$captain = __('A captain', true);
				}
				$this->set(compact('captain'));
				$this->set('player_options',
					$this->Game->_attendanceOptions ($team['id'], $role, $status, $past, false));
				$this->set('code', $this->_hash ($attendance));
				if (array_key_exists('note', $this->data['Person']) && !empty($this->data['Person']['note'])) {
					$this->set('note', $this->data['Person']['note']);
				}

				$this->_sendMail (array (
						'to' => $person,
						'replyTo' => $this->Session->read('Zuluru.Person'),
						'subject' => "{$team['name']} attendance change for game on $date",
						'template' => 'attendance_substitute_notification',
						'sendAs' => 'both',
				));
			}
		}

		return true;
	}

	function _updateAttendanceComment($team, $person, $date, $is_captain, $is_me, $attendance, $days_to_game, $past) {
		$comment = $this->data['Person']['comment'];
		if ($comment == $attendance['comment']) {
			return true;
		}

		if (!$this->Game->Attendance->saveField ('comment', $comment)) {
			$this->Session->setFlash(__('Failed to update the attendance comment!', true), 'default', array('class' => 'warning'));
			return false;
		}

		if (!$this->RequestHandler->isAjax()) {
			$this->Session->setFlash(sprintf (__('Attendance has been updated to %s.', true), $attendance_options[$status]), 'default', array('class' => 'success'));
		}

		// Maybe send some emails, only if the game is in the future
		if (!$past) {
			// Send email from the player to the captain if it's within the configured date range
			if ($is_me && $team['attendance_notification'] >= $days_to_game) {
				// Make sure the current player isn't in the list of captains to send to
				$captains = Set::extract ("/Person[id!={$person['id']}]", $team);
				if (!empty ($captains)) {
					$this->set('captains', implode (', ', Set::extract ('/Person/first_name', $captains)));
					$this->_sendMail (array (
							'to' => $captains,
							'replyTo' => $person,
							'subject' => "{$team['name']} attendance comment",
							'template' => 'attendance_comment_captain_notification',
							'sendAs' => 'both',
					));
				}
			}
		}

		return true;
	}

	function stat_sheet() {
		$id = $this->_arg('game');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$team_id = $this->_arg('team');
		if (!$team_id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$this->Game->contain(array(
			'Division' => array(
				'League' => array('StatType' => array('conditions' => array('StatType.type' => 'entered'))),
				'Day',
			),
			'HomeTeam',
			'HomePoolTeam' => 'DependencyPool',
			'AwayTeam',
			'AwayPoolTeam' => 'DependencyPool',
			'GameSlot' => array('Field' => 'Facility'),
		));
		$game = $this->Game->read(null, $id);
		if (!$game) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		if (!League::hasStats($game['Division']['League'])) {
			$this->Session->setFlash(__('This league does not have stat tracking enabled.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Configuration->loadAffiliate($game['Division']['League']['affiliate_id']);
		$this->Game->_readDependencies($game);
		if ($game['Game']['home_team'] == $team_id) {
			$team = $game['HomeTeam'];
			if ($game['Game']['away_team'] === null) {
				$opponent = array('name' => $game['Game']['away_dependency']);
			} else {
				$opponent = $game['AwayTeam'];
			}
		} else if ($game['Game']['away_team'] == $team_id) {
			$team = $game['AwayTeam'];
			if ($game['Game']['home_team'] === null) {
				$opponent = array('name' => $game['Game']['home_dependency']);
			} else {
				$opponent = $game['HomeTeam'];
			}
		} else {
			$this->Session->setFlash(__('That team is not playing in this game.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		if (!$team['track_attendance']) {
			$this->Session->setFlash(__('That team does not have attendance tracking enabled.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		if (Configure::read('feature.pdfize') && isset($this->Pdf)) {
			$this->Pdf->actionsToPdf = array($this->action);
		}

		$attendance = $this->Game->_read_attendance($team_id, Set::extract('/Division/Day/id', $game), $id);
		$this->set(compact('game', 'team', 'opponent', 'attendance'));
		$this->set('is_captain', in_array($team_id, $this->Session->read('Zuluru.OwnedTeamIDs')));
	}

	function live_score() {
		$this->layout = 'bare';

		$id = $this->_arg('game');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$team_id = $this->_arg('team');
		if (!$this->is_volunteer && !$team_id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$contain = array(
			'Division' => array(
				'League' => array(
					'StatType' => array('conditions' => array('StatType.type' => 'entered')),
				),
			),
			'GameSlot' => array('Field' => 'Facility'),
			'ScoreEntry',
			'ScoreDetail',
			// We need roster details for potential stat tracking.
			'HomeTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.role' => Configure::read('extended_playing_roster_roles')),
					'fields' => array(
						'Person.id', 'Person.first_name', 'Person.last_name', 'Person.gender',
					),
				),
			),
			'AwayTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.role' => Configure::read('extended_playing_roster_roles')),
					'fields' => array(
						'Person.id', 'Person.first_name', 'Person.last_name', 'Person.gender',
					),
				),
			),
		);
		if ($team_id) {
			$contain['ScoreEntry']['conditions'] = array('ScoreEntry.team_id' => $team_id);
		}

		$this->Game->contain ($contain);
		$game = $this->Game->read(null, $id);
		if (!$game) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Game->_adjustEntryIndices($game);

		if (!$game['Game']['home_team'] || !$game['Game']['away_team']) {
			$this->Session->setFlash(__('Dependencies for that game have not yet been resolved!', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'game' => $id));
		}

		if ($team_id && $team_id != $game['Game']['home_team'] && $team_id != $game['Game']['away_team']) {
			$this->Session->setFlash(__('That team did not play in that game!', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'game' => $id));
		}

		if ($this->Game->_is_finalized ($game)) {
			$this->Session->setFlash(__('The score for that game has already been finalized.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'game' => $id));
		}

		if ($team_id && array_key_exists($team_id, $game['ScoreEntry']) && $game['ScoreEntry'][$team_id]['status'] != 'in_progress') {
			$this->Session->setFlash(__('That team has already submitted a score for that game.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'game' => $id));
		}

		$this->Configuration->loadAffiliate($game['Division']['League']['affiliate_id']);
		Configure::load("sport/{$game['Division']['League']['sport']}");
		if ($game['Game']['home_team'] == $team_id || $team_id === null) {
			$team = $game['HomeTeam'];
			$opponent = $game['AwayTeam'];
		} else if ($game['Game']['away_team'] == $team_id) {
			$team = $game['AwayTeam'];
			$opponent = $game['HomeTeam'];
		} else {
			$this->Session->setFlash(__('That team is not playing in this game.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'game' => $id));
		}

		$this->set(compact ('game', 'team', 'opponent'));
		$this->set(array('submitter' => $team_id));
	}

	function score_up() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		$id = $this->_arg('game');
		if (!$id) {
			$this->set('error', sprintf(__('Invalid %s', true), __('game', true)));
			return;
		}

		$submitter = $this->_arg('team');
		if (!$this->is_volunteer && !$submitter) {
			$this->set('error', sprintf(__('Invalid %s', true), __('submitter', true)));
			return;
		}

		if (!$this->data['team_id']) {
			$this->set('error', sprintf(__('Invalid %s', true), __('team', true)));
			return;
		}

		// Lock all of this to prevent multiple simultaneous score updates
		// TODO: Handle both teams updating at the same time, one with details and one without
		if (!$this->Lock->lock ("live_scoring $id", null, null, false)) {
			$this->set('error', __('Someone else is currently updating the score for this game!\n\nIt\'s probably your opponent, try again right away.', true));
			return;
		}

		$this->Game->contain (array(
			'Division' => array(
				'League',
			),
			'HomeTeam',
			'AwayTeam',
			'ScoreEntry' => array('conditions' => array('ScoreEntry.team_id' => $submitter)),
			'ScoreDetail' => array('conditions' => array(
				'ScoreDetail.team_id' => $this->data['team_id'],
				'ScoreDetail.score_from' => $this->data['score_from'],
				'ScoreDetail.play' => $this->data['play'],
			)),
		));
		$game = $this->Game->read(null, $id);
		if (!$game) {
			$this->set('error', sprintf(__('Invalid %s', true), __('game', true)));
			return;
		}

		if ($this->data['team_id'] != $game['Game']['home_team'] && $this->data['team_id'] != $game['Game']['away_team']) {
			$this->set('error', __('That team did not play in that game!', true));
			return;
		}

		if ($this->Game->_is_finalized ($game)) {
			$this->set('error', __('The score for that game has already been finalized.', true));
			return;
		}

		// This will handle either the home team or a third-party submitting the score as "for"
		if (($submitter === null && $this->data['team_id'] == $game['Game']['home_team']) || $submitter == $this->data['team_id']) {
			$team_score_field = 'score_for';
			$opponent_score_field = 'score_against';
		} else {
			$team_score_field = 'score_against';
			$opponent_score_field = 'score_for';
		}

		if (!empty($game['ScoreEntry'])) {
			$entry = current($game['ScoreEntry']);
			if ($entry['status'] != 'in_progress') {
				$this->set('error', __('That team has already submitted a score for that game.', true));
				return;
			}
			unset($entry['created']);
			unset($entry['updated']);
			unset($entry['person_id']);
			$team_score = $entry[$team_score_field];
			$opponent_score = $entry[$opponent_score_field];
		} else {
			$entry = array(
				'team_id' => $submitter,
				'game_id' => $id,
				'status' => 'in_progress',
			);
			$team_score = $opponent_score = 0;
		}

		if ($team_score != $this->data['score_from']) {
			$this->set('error', __('The saved score does not match yours.\nSomeone else may have updated the score in the meantime.\n\nPlease refresh the page and try again.', true));
			return;
		}

		$this->Configuration->loadAffiliate($game['Division']['League']['affiliate_id']);
		Configure::load("sport/{$game['Division']['League']['sport']}");

		if (empty($this->data['play'])) {
			$this->set('error', __('You must indicate the scoring play so that the new score can be calculated.', true));
			return;
		}
		$points = Configure::read("sport.score_options.{$this->data['play']}");
		if (!$points) {
			$this->set('error', __('Invalid scoring play!', true));
			return;
		}
		$team_score += $points;
		$entry[$team_score_field] = $team_score;

		$transaction = new DatabaseTransaction($this->Game);

		if (!$this->Game->ScoreEntry->save($entry)) {
			$this->set('error', __('There was an error updating the score.\nPlease try again.', true));
			return;
		} else {
			$this->Game->updateAll(array('Game.updated' => 'NOW()'), array('Game.id' => $id));
		}

		// Check if there's already a matching score detail record (presumably from the other team).
		// If so, we may want to update it.
		if (!empty($game['ScoreDetail'])) {
			$this->Game->ScoreDetail->id = $game['ScoreDetail'][0]['id'];
		}
		if (!$this->Game->ScoreDetail->save(array_merge($this->data, array(
				'game_id' => $id,
				'created_team_id' => $submitter,
				'points' => $points,
		))))
		{
			$this->set('error', __('There was an error updating the box score.\nPlease try again.', true));
			return;
		}

		// Save stat details
		if (!empty($this->data['Stat'])) {
			foreach ($this->data['Stat'] as $stat_type_id => $person_id) {
				if (!empty($person_id)) {
					$this->Game->ScoreDetail->ScoreDetailStat->create();
					$this->Game->ScoreDetail->ScoreDetailStat->save(array(
							'score_detail_id' => $this->Game->ScoreDetail->id,
							'stat_type_id' => $stat_type_id,
							'person_id' => $person_id,
					));
				}
			}
		}

		$transaction->commit();

		$cache_file = CACHE . 'queries' . DS . "division_{$game['Division']['id']}.data";
		if (file_exists($cache_file) && time()-filemtime($cache_file) > 5 * MINUTE) {
			unlink($cache_file);
		}
		$cache_file = CACHE . 'queries' . DS . "schedule_{$game['Division']['id']}.data";
		if (file_exists($cache_file) && time()-filemtime($cache_file) > 5 * MINUTE) {
			unlink($cache_file);
		}

		if ($game['Game']['home_team'] == $this->data['team_id'] || $this->data['team_id'] === null) {
			$team = $game['HomeTeam'];
			$opponent = $game['AwayTeam'];
		} else if ($game['Game']['away_team'] == $this->data['team_id']) {
			$team = $game['AwayTeam'];
			$opponent = $game['HomeTeam'];
		}

		// Do some fun analysis on scores
		$twitter = "Score update #{$game['Division']['name']}: ";
		if ($team_score == 1 && $opponent_score == 0) {
			$twitter .= Team::twitterName($team) . ' opens the scoring against ' . Team::twitterName($opponent) . '.';
		} else if ($team_score >= $game['Division']['League']['expected_max_score']) {
			$twitter .= Team::twitterName($team) . " wins $team_score-$opponent_score against " . Team::twitterName($opponent);
		} else if ($team_score == ceil($game['Division']['League']['expected_max_score'] / 2) && $team_score > $opponent_score) {
			$twitter .= Team::twitterName($team) . " takes half $team_score-$opponent_score against " . Team::twitterName($opponent);
		} else if ($team_score == $opponent_score) {
			$twitter .= Team::twitterName($team) . ' scores to tie ' . Team::twitterName($opponent) . " at $team_score-$opponent_score";
			if ($team_score == $game['Division']['League']['expected_max_score'] - 1) {
				$twitter .= ', heading to overtime!';
			}
		} else if ($team_score == $opponent_score + 1) {
			$twitter .= Team::twitterName($team) . " takes the lead $team_score-$opponent_score against " . Team::twitterName($opponent);
		} else if ($team_score == $opponent_score - 1) {
			$twitter .= Team::twitterName($team) . " pulls within one, down $opponent_score-$team_score against " . Team::twitterName($opponent);
		} else if ($team_score == $opponent_score + 5) {
			$twitter .= Team::twitterName($team) . ' opens up a five-point lead on ' . Team::twitterName($opponent) . ', score now ' . Game::twitterScore($team, $team_score, $opponent, $opponent_score);
		} else {
			$twitter .= Game::twitterScore($team, $team_score, $opponent, $opponent_score);
		}

		$this->set(compact('team_score'));
		$this->set('twitter', addslashes($twitter));
	}

	function score_down() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		$id = $this->_arg('game');
		if (!$id) {
			$this->set('error', sprintf(__('Invalid %s', true), __('game', true)));
			return;
		}

		$submitter = $this->_arg('team');
		if (!$this->is_volunteer && !$submitter) {
			$this->set('error', sprintf(__('Invalid %s', true), __('submitter', true)));
			return;
		}

		if (!$this->data['team_id']) {
			$this->set('error', sprintf(__('Invalid %s', true), __('team', true)));
			return;
		}

		// Lock all of this to prevent multiple simultaneous score updates
		// TODO: Handle both teams updating at the same time, one with details and one without
		if (!$this->Lock->lock ("live_scoring $id", null, null, false)) {
			$this->set('error', __('Someone else is currently updating the score for this game!\n\nIt\'s probably your opponent, try again right away.', true));
			return;
		}

		$this->Game->contain (array(
			'Division' => array(
				'League',
			),
			'HomeTeam',
			'AwayTeam',
			'ScoreEntry' => array('conditions' => array('ScoreEntry.team_id' => $submitter)),
			'ScoreDetail' => array(
				'conditions' => array(
					'ScoreDetail.team_id' => $this->data['team_id'],
					'ScoreDetail.points !=' => null,
				),
				'order' => array('ScoreDetail.score_from' => 'DESC'),
			),
		));
		$game = $this->Game->read(null, $id);
		if (!$game) {
			$this->set('error', sprintf(__('Invalid %s', true), __('game', true)));
			return;
		}
		$this->Game->_adjustEntryIndices($game);

		if ($this->data['team_id'] != $game['Game']['home_team'] && $this->data['team_id'] != $game['Game']['away_team']) {
			$this->set('error', __('That team did not play in that game!', true));
			return;
		}

		if ($this->Game->_is_finalized ($game)) {
			$this->set('error', __('The score for that game has already been finalized.', true));
			return;
		}

		// This will handle either the home team or a third-party submitting the score as "for"
		if (($submitter === null && $this->data['team_id'] == $game['Game']['home_team']) || $submitter == $this->data['team_id']) {
			$team_score_field = 'score_for';
			$opponent_score_field = 'score_against';
		} else {
			$team_score_field = 'score_against';
			$opponent_score_field = 'score_for';
		}

		if (empty($game['ScoreEntry'])) {
			$this->set('error', __('You can\'t decrease the score below zero.', true));
			return;
		}
		$entry = current($game['ScoreEntry']);
		if ($entry['status'] != 'in_progress') {
			$this->set('error', __('That team has already submitted a score for that game.', true));
			return;
		}
		unset($entry['created']);
		unset($entry['updated']);
		unset($entry['person_id']);
		$team_score = $entry[$team_score_field];
		$opponent_score = $entry[$opponent_score_field];

		if ($team_score != $this->data['score_from']) {
			$this->set('error', __('The saved score does not match yours.\nSomeone else may have updated the score in the meantime.\n\nPlease refresh the page and try again.', true));
			return;
		}

		$this->Configuration->loadAffiliate($game['Division']['League']['affiliate_id']);
		Configure::load("sport/{$game['Division']['League']['sport']}");

		$detail = array_shift($game['ScoreDetail']);
		$team_score -= $detail['points'];
		$entry[$team_score_field] = $team_score;

		$transaction = new DatabaseTransaction($this->Game);

		if (!$this->Game->ScoreEntry->save($entry)) {
			$this->set('error', __('There was an error updating the score.\nPlease try again.', true));
			return;
		} else {
			$this->Game->updateAll(array('Game.updated' => 'NOW()'), array('Game.id' => $id));
		}

		// Delete the matching score detail record, if it's got details from our team.
		// TODO: If the other team isn't keeping stats, there might be ScoreDetail records to remove when the score is finalized.
		if (($submitter === null || $detail['team_id'] == $submitter) && !$this->Game->ScoreDetail->delete($detail['id'])) {
			$this->set('error', __('There was an error updating the box score.\nPlease try again.', true));
			return;
		}
		$transaction->commit();

		$cache_file = CACHE . 'queries' . DS . "division_{$game['Division']['id']}.data";
		if (file_exists($cache_file) && time()-filemtime($cache_file) > 5 * MINUTE) {
			unlink($cache_file);
		}
		$cache_file = CACHE . 'queries' . DS . "schedule_{$game['Division']['id']}.data";
		if (file_exists($cache_file) && time()-filemtime($cache_file) > 5 * MINUTE) {
			unlink($cache_file);
		}

		if ($game['Game']['home_team'] == $this->data['team_id'] || $this->data['team_id'] === null) {
			$team = $game['HomeTeam'];
			$opponent = $game['AwayTeam'];
		} else if ($game['Game']['away_team'] == $this->data['team_id']) {
			$team = $game['AwayTeam'];
			$opponent = $game['HomeTeam'];
		}

		$twitter = "Score update #{$game['Division']['name']}: " . Game::twitterScore($team, $team_score, $opponent, $opponent_score);
		$this->set(compact('team_score'));
		$this->set('twitter', addslashes($twitter));
	}

	function timeout() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		$id = $this->_arg('game');
		if (!$id) {
			$this->set('error', sprintf(__('Invalid %s', true), __('game', true)));
			return;
		}

		$submitter = $this->_arg('team');
		if (!$this->is_volunteer && !$submitter) {
			$this->set('error', sprintf(__('Invalid %s', true), __('submitter', true)));
			return;
		}

		if (!$this->data['team_id']) {
			$this->set('error', sprintf(__('Invalid %s', true), __('team', true)));
			return;
		}

		// Lock all of this to prevent multiple simultaneous score updates
		if (!$this->Lock->lock ("live_scoring $id", null, null, false)) {
			$this->set('error', __('Someone else is currently updating the score for this game!\n\nIt\'s probably your opponent, try again right away.', true));
			return;
		}

		$this->Game->contain (array(
			'Division' => array(
				'League',
			),
			'HomeTeam',
			'AwayTeam',
			'ScoreEntry' => array('conditions' => array('ScoreEntry.team_id' => $submitter)),
			'ScoreDetail' => array('conditions' => array(
				'ScoreDetail.team_id' => $this->data['team_id'],
				'ScoreDetail.play' => 'Timeout',
			)),
		));
		$game = $this->Game->read(null, $id);
		if (!$game) {
			$this->set('error', sprintf(__('Invalid %s', true), __('game', true)));
			return;
		}
		$this->Game->_adjustEntryIndices($game);

		if ($this->data['team_id'] != $game['Game']['home_team'] && $this->data['team_id'] != $game['Game']['away_team']) {
			$this->set('error', __('That team did not play in that game!', true));
			return;
		}

		if ($this->Game->_is_finalized ($game)) {
			$this->set('error', __('The score for that game has already been finalized.', true));
			return;
		}

		// This will handle either the home team or a third-party submitting the timeout as "for"
		if (($submitter === null && $this->data['team_id'] == $game['Game']['home_team']) || $submitter == $this->data['team_id']) {
			$team_score_field = 'score_for';
			$opponent_score_field = 'score_against';
		} else {
			$team_score_field = 'score_against';
			$opponent_score_field = 'score_for';
		}

		if (empty($game['ScoreEntry'])) {
			$team_score = $opponent_score = 0;
		} else {
			$entry = current($game['ScoreEntry']);
			if ($entry['status'] != 'in_progress') {
				$this->set('error', __('That team has already submitted a score for that game.', true));
				return;
			}
			$team_score = $entry[$team_score_field];
			$opponent_score = $entry[$opponent_score_field];
		}
		if ($team_score != $this->data['score_from']) {
			$this->set('error', __('The saved score does not match yours.\nSomeone else may have updated the score in the meantime.\n\nPlease refresh the page and try again.', true));
			return;
		}

		$this->Configuration->loadAffiliate($game['Division']['League']['affiliate_id']);
		Configure::load("sport/{$game['Division']['League']['sport']}");

		if ($game['Game']['home_team'] == $this->data['team_id'] || $this->data['team_id'] === null) {
			$team = $game['HomeTeam'];
			$opponent = $game['AwayTeam'];
		} else if ($game['Game']['away_team'] == $this->data['team_id']) {
			$team = $game['AwayTeam'];
			$opponent = $game['HomeTeam'];
		}

		$twitter = "Game update #{$game['Division']['name']}: timeout called by " . Team::twitterName($team) . ' with the score ' . Game::twitterScore($team, $team_score, $opponent, $opponent_score);

		// Check if there's already a score detail record from the other team that this is likely a duplicate of.
		// If so, we want to disregard it.
		foreach ($game['ScoreDetail'] as $detail) {
			if ($detail['play'] == 'Timeout' &&
				$detail['created_team_id'] != $submitter &&
				$detail['score_from'] == $this->data['score_from'] &&
				strtotime($detail['created']) >= time() - 2 * MINUTE)
			{
				$this->set('taken', count($game['ScoreDetail']));
				$this->set('twitter', addslashes($twitter));
				return;
			}
		}

		if (!$this->Game->ScoreDetail->save(array_merge($this->data, array(
				'game_id' => $id,
				'created_team_id' => $submitter,
				'play' => 'Timeout',
		))))
		{
			$this->set('error', __('There was an error updating the box score.\nPlease try again.', true));
			return;
		}

		$this->set('taken', count($game['ScoreDetail']) + 1);
		$this->set('twitter', addslashes($twitter));
	}

	function play() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		$id = $this->_arg('game');
		if (!$id) {
			$this->set('message', sprintf(__('Invalid %s', true), __('game', true)));
			return;
		}

		$submitter = $this->_arg('team');
		if (!$this->is_volunteer && !$submitter) {
			$this->set('message', sprintf(__('Invalid %s', true), __('submitter', true)));
			return;
		}

		if (!$this->data['team_id']) {
			$this->set('message', sprintf(__('Invalid %s', true), __('team', true)));
			return;
		}

		// Lock all of this to prevent multiple simultaneous score updates
		if (!$this->Lock->lock ("live_scoring $id", null, null, false)) {
			$this->set('message', __('Someone else is currently updating the score for this game!\n\nIt\'s probably your opponent, try again right away.', true));
			return;
		}

		$this->Game->contain (array(
			'Division' => array(
				'League',
			),
			'HomeTeam',
			'AwayTeam',
			'ScoreEntry' => array('conditions' => array('ScoreEntry.team_id' => $submitter)),
			'ScoreDetail',
		));
		$game = $this->Game->read(null, $id);
		if (!$game) {
			$this->set('message', sprintf(__('Invalid %s', true), __('game', true)));
			return;
		}
		$this->Game->_adjustEntryIndices($game);

		if ($this->data['team_id'] != $game['Game']['home_team'] && $this->data['team_id'] != $game['Game']['away_team']) {
			$this->set('message', __('That team did not play in that game!', true));
			return;
		}

		if ($this->Game->_is_finalized ($game)) {
			$this->set('message', __('The score for that game has already been finalized.', true));
			return;
		}

		// This will handle either the home team or a third-party submitting the score as "for"
		if (($submitter === null && $this->data['team_id'] == $game['Game']['home_team']) || $submitter == $this->data['team_id']) {
			$team_score_field = 'score_for';
			$opponent_score_field = 'score_against';
		} else {
			$team_score_field = 'score_against';
			$opponent_score_field = 'score_for';
		}

		if (empty($game['ScoreEntry'])) {
			$team_score = $opponent_score = 0;
		} else {
			$entry = current($game['ScoreEntry']);
			if ($entry['status'] != 'in_progress') {
				$this->set('message', __('That team has already submitted a score for that game.', true));
				return;
			}
			$team_score = $entry[$team_score_field];
			$opponent_score = $entry[$opponent_score_field];
		}

		$this->Configuration->loadAffiliate($game['Division']['League']['affiliate_id']);
		Configure::load("sport/{$game['Division']['League']['sport']}");
		$sport_obj = $this->_getComponent ('Sport', $game['Division']['League']['sport'], $this);

		if (empty($this->data['play'])) {
			$this->set('message', __('You must indicate the play so that the box score will be accurate.', true));
			return;
		}
		if ($this->data['play'] != 'Start' && !Configure::read("sport.other_options.{$this->data['play']}")) {
			$this->set('message', __('Invalid play!', true));
			return;
		}

		if ($game['Game']['home_team'] == $this->data['team_id'] || $this->data['team_id'] === null) {
			$team = $game['HomeTeam'];
			$opponent = $game['AwayTeam'];
		} else if ($game['Game']['away_team'] == $this->data['team_id']) {
			$team = $game['AwayTeam'];
			$opponent = $game['HomeTeam'];
		}

		$valid = $sport_obj->validate_play($this->data['team_id'], $this->data['play'], $this->data['score_from'], $game['ScoreDetail']);
		if ($valid !== true) {
			$this->set('message', addslashes($valid));
			$this->set('twitter', '');
			return;
		} else if ($this->data['play'] == 'Start') {
			$this->set('message', __('Game timer initialized.', true));
			$twitter = "Game update #{$game['Division']['name']}: " . Team::twitterName($team) . ' pulls to ' . Team::twitterName($opponent) . ' to start the game.';
			$this->set('twitter', addslashes($twitter));
		} else {
			$this->set('message', Configure::read("sport.other_options.{$this->data['play']}") . ' ' . __('recorded', true));
			$twitter = "Game update #{$game['Division']['name']}: " . Team::twitterName($team) . ' ' . low(Configure::read("sport.other_options.{$this->data['play']}")) . ' vs ' . Team::twitterName($opponent);
			$this->set('twitter', addslashes($twitter));
		}

		// Check if there's already a score detail record from the other team that this is likely a duplicate of.
		// If so, we want to disregard it.
		foreach ($game['ScoreDetail'] as $detail) {
			if ($detail['play'] == $this->data['play'] &&
				$detail['team_id'] == $this->data['team_id'] &&
				$detail['created_team_id'] != $submitter &&
				$detail['score_from'] == $this->data['score_from'] &&
				strtotime($detail['created']) >= time() - 2 * MINUTE)
			{
				return;
			}
		}

		if (!$this->Game->ScoreDetail->save(array_merge($this->data, array(
				'game_id' => $id,
				'created_team_id' => $submitter,
		))))
		{
			$this->set('message', __('There was an error updating the box score.\nPlease try again.', true));
			return;
		}
	}

	function tweet() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		if (!App::import('Lib', 'twitter_api_exchange')) {
			$this->set('message', sprintf(__('Failed to load the %s library! Contact your system administrator.', true), 'Twitter API Exchange'));
			return;
		}
		$this->Game->HomeTeam->Person->contain();
		$person = $this->Game->HomeTeam->Person->read(array('twitter_token', 'twitter_secret'), $this->Auth->user('id'));
		if (empty($person['Person']['twitter_token']) || empty($person['Person']['twitter_secret'])) {
			$this->set('message', __('You have not authorized this site to tweet on your behalf. Configure this in the Profile Preferences page.', true));
			return;
		}
		$settings = array(
				'consumer_key' => Configure::read('twitter.consumer_key'),
				'consumer_secret' => Configure::read('twitter.consumer_secret'),
				'oauth_access_token' => $person['Person']['twitter_token'],
				'oauth_access_token_secret' => $person['Person']['twitter_secret'],
		);
		$url = 'https://api.twitter.com/1.1/statuses/update.json';
		$postfields = array(
				'status' => $this->data['Twitter']['message'],
				'lat' => $this->data['Twitter']['lat'],
				'long' => $this->data['Twitter']['long'],
		);
		$twitter = new TwitterAPIExchange($settings);
		$response = json_decode($twitter->buildOauth($url, 'POST')->setPostfields($postfields)->performRequest());
		if (!empty($response->id_str)) {
			$this->set('message', __('Your message has been tweeted.', true));
		} else {
			$this->set('message', __('Failed to send the tweet.', true) . ' ' . $response->errors[0]->message);
		}
	}

	function submit_score() {
		$id = $this->_arg('game');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$team_id = $this->_arg('team');
		if (!$team_id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$contain = array (
			'Division' => array(
				'Person' => array('fields' => array('id', 'first_name', 'last_name', 'email')),
				'League',
			),
			'GameSlot' => array('Field' => 'Facility'),
			'ScoreEntry' => array('Person' => array('fields' => array('id', 'first_name', 'last_name'))),
			'SpiritEntry',
			'Incident',
		);
		if (Configure::read('scoring.allstars')) {
			// We need roster details for potential allstar nominations.
			$contain = array_merge($contain, array(
				'HomeTeam' => array(
					'Person' => array(
						'conditions' => array('TeamsPerson.role' => Configure::read('extended_playing_roster_roles')),
						'fields' => array(
							'Person.id', 'Person.first_name', 'Person.last_name', 'Person.email', 'Person.gender',
						),
					),
				),
				'AwayTeam' => array(
					'Person' => array(
						'conditions' => array('TeamsPerson.role' => Configure::read('extended_playing_roster_roles')),
						'fields' => array(
							'Person.id', 'Person.first_name', 'Person.last_name', 'Person.email', 'Person.gender',
						),
					),
				),
				'Allstar' => array('Person'),
			));
		} else {
			$contain = array_merge($contain, array(
				'HomeTeam' => array(
					'Person' => array(
						'conditions' => array('TeamsPerson.role' => Configure::read('privileged_roster_roles')),
						'fields' => array(
							'Person.id', 'Person.first_name', 'Person.last_name', 'Person.email', 'Person.gender',
						),
					),
				),
				'AwayTeam' => array(
					'Person' => array(
						'conditions' => array('TeamsPerson.role' => Configure::read('privileged_roster_roles')),
						'fields' => array(
							'Person.id', 'Person.first_name', 'Person.last_name', 'Person.email', 'Person.gender',
						),
					),
				),
			));
		}

		$this->Game->contain ($contain);
		$game = $this->Game->read(null, $id);
		if (!$game) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Configuration->loadAffiliate($game['Division']['League']['affiliate_id']);
		Configure::load("sport/{$game['Division']['League']['sport']}");
		$this->Game->_adjustEntryIndices($game);
		if ($game['Game']['home_team'] == $team_id) {
			$team = $game['HomeTeam'];
			$opponent = $game['AwayTeam'];
		} else if ($game['Game']['away_team'] == $team_id) {
			$team = $game['AwayTeam'];
			$opponent = $game['HomeTeam'];
		} else {
			$this->Session->setFlash(__('That team is not playing in this game.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'game' => $id));
		}

		$end_time = strtotime("{$game['GameSlot']['game_date']} {$game['GameSlot']['display_game_end']}") +
				Configure::read('timezone.adjust') * 60;
		if ($end_time - 60 * 60 > time()) {
			$this->Session->setFlash(__('That game has not yet occurred!', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'game' => $id));
		}

		if ($this->Game->_is_finalized ($game)) {
			$this->Session->setFlash(__('The score for that game has already been finalized.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'game' => $id));
		}

		if (empty($game['Game']['home_team']) || empty($game['Game']['away_team'])) {
			$this->Session->setFlash(__('The opponent for that game has not been determined, so a score cannot yet be submitted.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'game' => $id));
		}

		if ($this->Game->_get_score_entry ($game, $team_id)) {
			// We have to use string 1 and 0 here, as that's what the
			// form helper checkbox function compares against, using ===
			$game['Game']['allstar'] = (!empty ($game['Allstar']) ? '1' : '0');
			$game['Game']['incident'] = (!empty ($game['Incident']) ? '1' : '0');
		}

		// We need this in a couple of places
		$spirit_obj = $this->_getComponent ('Spirit', $game['Division']['League']['sotg_questions'], $this);

		if (!empty ($this->data)) {
			$transaction = new DatabaseTransaction($this->Game);

			// We could put these as hidden fields in the form, but we'd need to
			// validate them against the values from the URL anyway, so it's
			// easier to just set them directly here.
			// We use the team_id as the array index, here and in the views,
			// because order matters, and this is a good way to ensure that
			// the correct data gets into the correct form.
			$this->data['Game']['id'] = $id;
			$this->data['ScoreEntry'][$team_id]['team_id'] = $team_id;
			$unplayed = in_array($this->data['ScoreEntry'][$team_id]['status'], Configure::read('unplayed_status'));
			if (!$unplayed) {
				$this->_spiritTeams ($opponent['id'], $team_id, $this->data);
			} else {
				unset($this->data['Allstar']);
				unset($this->data['SpiritEntry']);
			}

			// Ensure that the saved score entry ids (if any) are the same as the posted ids (if any)
			$saved = $posted = null;
			if (!empty($game['ScoreEntry'][$team_id]['id'])) {
				$saved = $game['ScoreEntry'][$team_id]['id'];
			}
			if (array_key_exists ('id', $this->data['ScoreEntry'][$team_id])) {
				$posted = $this->data['ScoreEntry'][$team_id]['id'];
			}
			if ($saved !== $posted) {
				if (!$posted) {
					$this->Session->setFlash(__('There is already a score submitted by your team for this game. To update this, use the "edit" link.', true), 'default', array('class' => 'info'));
				} else {
					$this->Session->setFlash(__('ID for posted score does not match the saved ID.', true), 'default', array('class' => 'error'));
				}
				$this->redirect(array('action' => 'view', 'game' => $id));
			}

			// Same process, for spirit entries
			if (!$unplayed) {
				$saved = $posted = null;
				if (!empty($game['SpiritEntry'][$opponent['id']]['id'])) {
					$saved = $game['SpiritEntry'][$opponent['id']]['id'];
				}
				if (array_key_exists ('id', $this->data['SpiritEntry'][$opponent['id']])) {
					$posted = $this->data['SpiritEntry'][$opponent['id']]['id'];
				}
				if ($saved !== $posted) {
					if (!$posted) {
						$this->Session->setFlash(__('There is already a spirit score submitted by your team for this game. To update this, use the "edit" link.', true), 'default', array('class' => 'info'));
					} else {
						$this->Session->setFlash(__('ID for posted spirit score does not match the saved ID.', true), 'default', array('class' => 'error'));
					}
					$this->redirect(array('action' => 'view', 'game' => $id));
				}
			}

			if (Configure::read('scoring.allstars') &&
				$game['Division']['allstars'] != 'never' &&
				array_key_exists ('Allstar', $this->data))
			{
				if ($game['Division']['allstars_from'] == 'submitter') {
					$roster = Set::extract('/Person/id', $team);
				} else {
					$roster = Set::extract('/Person/id', $opponent);
				}

				foreach ($this->data['Allstar'] as $key => $data) {
					if (!$data['person_id']) {
						// Delete any pre-existing nominations that have been removed
						if (array_key_exists ('id', $data)) {
							$this->Game->Allstar->delete ($data['id']);
						}
						// Remove blank all-star fields, as they will cause insertion errors
						unset ($this->data['Allstar'][$key]);
					} else {
						// Validate that the all-star submissions are on the correct roster
						if (!in_array($data['person_id'], $roster)) {
							$this->Session->setFlash(__('You have tried to perform an invalid all-star nomination.', true), 'default', array('class' => 'error'));
							$this->redirect('/');
						}
					}
				}
				if (empty ($this->data['Allstar'])) {
					unset ($this->data['Allstar']);
				}
			} else {
				unset ($this->data['Allstar']);
			}

			// Remove blank incident reports, as they will cause insertion errors
			if (Configure::read('scoring.incident_reports') &&
				array_key_exists ('incident', $this->data['Game']) &&
				$this->data['Game']['incident'])
			{
				$this->data['Incident'][$team_id]['team_id'] = $team_id;
			} else {
				unset ($this->data['Incident']);
			}

			// Set default values in the case of a default reported
			$status = $this->data['ScoreEntry'][$team_id]['status'];
			if ($status == 'home_default') {
				if ($game['Game']['home_team'] == $team_id) {
					$this->data['ScoreEntry'][$team_id]['score_for'] = Configure::read('scoring.default_losing_score');
					$this->data['ScoreEntry'][$team_id]['score_against'] = Configure::read('scoring.default_winning_score');
					$this->_spiritMerge ($opponent['id'], $spirit_obj->expected(), $this->data);
				} else {
					$this->data['ScoreEntry'][$team_id]['score_for'] = Configure::read('scoring.default_winning_score');
					$this->data['ScoreEntry'][$team_id]['score_against'] = Configure::read('scoring.default_losing_score');
					$this->_spiritMerge ($opponent['id'], $spirit_obj->defaulted(), $this->data);
				}
			} else if ($status == 'away_default') {
				if ($game['Game']['home_team'] == $team_id) {
					$this->data['ScoreEntry'][$team_id]['score_for'] = Configure::read('scoring.default_winning_score');
					$this->data['ScoreEntry'][$team_id]['score_against'] = Configure::read('scoring.default_losing_score');
					$this->_spiritMerge ($opponent['id'], $spirit_obj->defaulted(), $this->data);
				} else {
					$this->data['ScoreEntry'][$team_id]['score_for'] = Configure::read('scoring.default_losing_score');
					$this->data['ScoreEntry'][$team_id]['score_against'] = Configure::read('scoring.default_winning_score');
					$this->_spiritMerge ($opponent['id'], $spirit_obj->expected(), $this->data);
				}
			} else if ($unplayed) {
				$this->data['ScoreEntry'][$team_id]['score_for'] = $this->data['ScoreEntry'][$team_id]['score_against'] = null;
			}

			// Spirit score entry validation comes from the spirit component
			$this->Game->SpiritEntry->validate = $spirit_obj->getValidate($game['Division']['League']);

			$resultMessage = null;
			if ($this->Game->saveAll($this->data, array('validate' => 'first'))) {
				// If the game was unplayed, and there's a spirit entry from a previous submission,
				// we must delete that entry.
				if ($unplayed && !empty($game['SpiritEntry'][$opponent['id']]['id'])) {
					$this->Game->SpiritEntry->delete($game['SpiritEntry'][$opponent['id']]['id'], false);
				}
				$transaction->commit();

				$cache_file = CACHE . 'queries' . DS . "division_{$game['Division']['id']}.data";
				if (file_exists($cache_file)) {
					unlink($cache_file);
				}
				$cache_file = CACHE . 'queries' . DS . "schedule_{$game['Division']['id']}.data";
				if (file_exists($cache_file)) {
					unlink($cache_file);
				}

				// Check if the opponent has an entry
				if (!$this->Game->_get_score_entry($game, $opponent['id'])) {
					// No, so we just mention that it's been saved and move on
					$status = $this->data['ScoreEntry'][$team_id]['status'];
					if (in_array($status, Configure::read('unplayed_status'))) {
						$team_status = $opponent_status = __($status, true);
					} else {
						$score_for = $this->data['ScoreEntry'][$team_id]['score_for'];
						$score_against = $this->data['ScoreEntry'][$team_id]['score_against'];
						$default = (strpos($status, 'default') !== false);
						if ($score_for > $score_against) {
							$team_status = __('a win for your team', true);
							if ($default) {
								$opponent_status = __('a default loss for your team', true);
							} else {
								$opponent_status = sprintf(__('a %s-%s loss for your team', true), $score_for, $score_against);
							}
						} else if ($score_for < $score_against) {
							$team_status = __('a loss for your team', true);
							if ($default) {
								$opponent_status = __('a default win for your team', true);
							} else {
								$opponent_status = sprintf(__('a %s-%s win for your team', true), $score_against, $score_for);
							}
						} else {
							$team_status = __('a tie', true);
							$opponent_status = sprintf(__('a %s-%s tie', true), $score_for, $score_against);
						}
					}
					$resultMessage = sprintf(__('This score has been saved. Once your opponent has entered their score, it will be officially posted.<br/><br/>The score you have submitted indicates that this game was %s. If this is incorrect, you can edit the score to correct it.', true), $team_status);
					$resultClass = 'success';

					// Email opposing captains with this score and an easy link					
					$captains = array();
					foreach (Configure::read('privileged_roster_roles') as $role) {
						$captains = array_merge($captains, Set::extract ("/Person/TeamsPerson[role=$role]/..", $opponent));
					}
					if (!empty($captains)) {
						$division = $game['Division'];
						// We need to swap the for and against scores to reflect the opponent's view
						list($score_against, $score_for) = array($score_for, $score_against);
						$this->set(compact ('division', 'game', 'status', 'opponent_status', 'score_for', 'score_against', 'team', 'opponent', 'captains'));
						$this->_sendMail (array (
								'to' => $captains,
								'from' => $this->Session->read('Zuluru.Person.email_formatted'),
								'subject' => 'Opponent score submission',
								'template' => 'score_submission',
								'sendAs' => 'both',
						));
					}
				} else {
					// Otherwise, both teams have an entry.  So, attempt to finalize using
					// this information.
					$result = $this->_finalize($id);
					if ($result === true) {
						$resultMessage = __('This score agrees with the score submitted by your opponent. It will now be posted as an official game result.', true);
						$resultClass = 'success';
					} else {
						$resultMessage = $result;
						$resultClass = 'warning';
					}
				}

				// TODO: Check for changes to the incident text to avoid sending a duplicate email,
				// and we probably want to change the text of the email slightly to let the recipient
				// know that it's an update instead of a new incident.
				if (Configure::read('scoring.incident_reports') && $this->data['Game']['incident']) {
					$addr = Configure::read('email.incident_report_email');
					$incident = $this->data['Incident'][$team_id];
					$this->set(compact ('game', 'incident'));
					if ($this->_sendMail (array (
							'to' => "Incident Manager <$addr>",
							'from' => $this->Session->read('Zuluru.Person.email_formatted'),
							'subject' => "Incident report: {$incident['type']}",
							'template' => 'incident_report',
							'sendAs' => 'html',
					)))
					{
						// TODO: Maybe send the incident report before saving data, and add in a column for
						// whether it was sent, thus allowing the cron to attempt to re-send it?
						$resultMessage .= ' ' . __('Your incident report details have been sent for handling.', true);
					} else {
						App::import('Helper', 'Html');
						$html = new HtmlHelper();
						$link = $html->link($addr, "mailto:$addr");
						$resultMessage .= ' ' . sprintf(__('There was an error sending your incident report details. Please send them to %s to ensure proper handling.', true), $link);
						$resultClass = 'warning';
					}
				}

				if ($resultMessage) {
					$this->Session->setFlash($resultMessage, 'default', array('class' => $resultClass));
				}

				if (League::hasStats($game['Division']['League']) && $this->data['Game']['collect_stats']) {
					$this->redirect(array('action' => 'submit_stats', 'game' => $id, 'team' => $team_id));
				} else {
					$this->redirect('/');
				}
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('game results', true)), 'default', array('class' => 'warning'));
			}
		} else {
			$this->data = $game;
			if (array_key_exists('status', $this->params['named'])) {
				$this->data['ScoreEntry'][$team_id] = $this->params['named'];
			}
		}

		$this->set(compact ('game', 'team_id', 'spirit_obj'));
		$this->set('is_coordinator', in_array ($game['Division']['id'], $this->Session->read('Zuluru.DivisionIDs')));
	}

	function submit_stats() {
		$id = $this->_arg('game');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$this->Game->contain (array (
			'Division' => array(
				'League' => array('StatType' => array('conditions' => array('StatType.type' => 'entered'))),
				'Day',
			),
			'GameSlot' => array('Field' => 'Facility'),
			'ScoreEntry',
			'ScoreDetail' => array('ScoreDetailStat'),
			'HomeTeam',
			'AwayTeam',
		));

		$game = $this->Game->read(null, $id);
		if (!$game) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$team_id = $this->_arg('team');
		// Allow specified individuals (referees, umpires, volunteers) to submit stats without a team id
		if (!$this->is_volunteer && !$team_id && !in_array($game['Division']['id'], $this->Session->read('Zuluru.DivisionIDs'))) {
			$this->Session->setFlash(__('You must provide a team ID.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		if (!League::hasSpirit($game['Division']['League'])) {
			$this->Session->setFlash(__('That league does not have stat tracking enabled!', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		if ($team_id && $team_id != $game['Game']['home_team'] && $team_id != $game['Game']['away_team']) {
			$this->Session->setFlash(__('That team did not play in that game!', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		if (!Game::_is_finalized($game)) {
			$this->Game->_adjustEntryIndices($game);
			if ($team_id && !array_key_exists($team_id, $game['ScoreEntry'])) {
				$this->Session->setFlash(__('You must submit a score for this game before you can submit stats.', true), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'submit_score', 'game' => $id, 'team' => $team_id));
			}
		}

		$end_time = strtotime("{$game['GameSlot']['game_date']} {$game['GameSlot']['display_game_end']}") +
				Configure::read('timezone.adjust') * 60;
		if ($end_time - 60 * 60 > time()) {
			$this->Session->setFlash(__('That game has not yet occurred!', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$this->Configuration->loadAffiliate($game['Division']['League']['affiliate_id']);
		Configure::load("sport/{$game['Division']['League']['sport']}");
		$sport_obj = $this->_getComponent ('Sport', $game['Division']['League']['sport'], $this);

		// Remove any empty stats. We DON'T remove '0' stats, as that's still a stat.
		$teams = array_unique(Set::extract('/Stat/team_id', $this->data));
		$entry_stats = Set::extract('/StatType/id', $game['Division']['League']);
		if (!empty($this->data)) {
			$had_data = true;
			foreach ($this->data['Stat'] as $key => $datum) {
				if ($datum['value'] === '' || !in_array($datum['stat_type_id'], $entry_stats)) {
					unset($this->data['Stat'][$key]);
				}
			}

			// Locate existing records that we want to delete
			$to_delete = $this->Game->Stat->find('list', array(
					'conditions' => array(
						'game_id' => $id,
						'team_id' => $teams,
						'NOT' => array('id' => Set::extract('/Stat/id', $this->data)),
					),
					'contain' => array(),
			));
		}

		if (!empty($to_delete) || !empty($this->data['Stat'])) {
			$transaction = new DatabaseTransaction($this->Game->Stat);
		}

		if (!empty($to_delete)) {
			if (!$this->Game->Stat->deleteAll(array('Stat.id' => $to_delete))) {
				$this->Session->setFlash(sprintf(__('Failed to delete previously saved %s', true), __('stats', true)), 'default', array('class' => 'error'));
				unset($transaction);
			} else {
				// This will be overridden, unless the user erased all previous stats and didn't enter new ones
				$this->Session->setFlash(sprintf(__('The previously saved %s have been removed.', true), __('stats', true)), 'default', array('class' => 'success'));
			}
		}

		if (!empty($this->data['Stat'])) {
			if (isset($transaction)) {
				// Add calculated stats to the array to be saved. We will have deleted any prior calculated stats above.
				$calc_stats = $this->Game->Division->League->StatType->find('all', array(
						'contain' => array(),
						'conditions' => array(
							'StatType.type' => 'game_calc',
							'StatType.sport' => $game['Division']['League']['sport'],
						),
						'fields' => array('id', 'handler'),
				));
				foreach ($calc_stats as $stat_type) {
					$func = "{$stat_type['StatType']['handler']}_game";
					if (method_exists($sport_obj, $func)) {
						$sport_obj->$func($stat_type['StatType'], $game, $this->data);
					} else {
						trigger_error("Game stat handler {$stat_type['StatType']['handler']} was not found in the {$game['Division']['League']['sport']} component!", E_USER_ERROR);
					}
				}

				if ($this->Game->Stat->saveAll($this->data['Stat'], array('validate' => 'first'))) {
					$this->Session->setFlash(sprintf(__('The %s have been saved', true), __('stats', true)), 'default', array('class' => 'success'));
					$transaction->commit();

					if ($team_id) {
						$cache_file = CACHE . 'queries' . DS . "team_stats_{$team_id}.data";
						if (file_exists($cache_file)) {
							unlink($cache_file);
						}
					} else {
						$cache_file = CACHE . 'queries' . DS . "team_stats_{$game['Game']['home_team']}.data";
						if (file_exists($cache_file)) {
							unlink($cache_file);
						}
						$cache_file = CACHE . 'queries' . DS . "team_stats_{$game['Game']['away_team']}.data";
						if (file_exists($cache_file)) {
							unlink($cache_file);
						}
					}

					$this->redirect(array('action' => 'view', 'game' => $id));
				} else {
					$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('stats', true)), 'default', array('class' => 'warning'));
				}
			} else {
				// The deletion above failed, so we don't want to try to save the other data,
				// but let's validate it anyway, in case there's errors we can report.
				$this->Game->Stat->saveAll($this->data['Stat'], array('validate' => 'only'));
			}
		} else {
			if (isset($transaction)) {
				// This will happen if stats were previously saved but all were erased in the input form.
				// Since there was no new data to save, commit the deletions.
				$transaction->commit();
				$this->redirect(array('action' => 'view', 'game' => $id));
			} else if (isset($had_data)) {
				// This will happen if an empty form was submitted.
				$this->Session->setFlash(__('You did not submit any stats. You can return to complete this at any time.', true), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'view', 'game' => $id));
			}

			$this->data = $this->Game->Stat->find('all', array(
					'contain' => array(),
					'conditions' => array('game_id' => $id),
			));
			if (empty($this->data)) {
				// Extract counts of stats per player from the live scoring
				$this->data['Stat'] = array();
				foreach ($game['ScoreDetail'] as $detail) {
					foreach ($detail['ScoreDetailStat'] as $stat) {
						$key = "{$stat['person_id']}:{$stat['stat_type_id']}";
						if (!array_key_exists($key, $this->data['Stat'])) {
							$this->data['Stat'][$key] = array(
								'game_id' => $id,
								'team_id' => $detail['team_id'],
								'person_id' => $stat['person_id'],
								'stat_type_id' => $stat['stat_type_id'],
								'value' => 1,
							);
						} else {
							++ $this->data['Stat'][$key]['value'];
						}
					}
				}
				$this->data['Stat'] = array_values($this->data['Stat']);
			} else {
				// Reformat the data into the form needed for saving multiple records,
				// which is the same form the display expects, in case errors cause it
				// to be redisplayed.
				$this->data = array('Stat' => Set::extract('/Stat/.', $this->data));
			}
		}

		if ($team_id) {
			$attendance = $this->Game->_read_attendance($team_id, Set::extract('/Division/Day/id', $game), $id, null, true);
			usort ($attendance['Person'], array('Person', 'comparePerson'));
		} else {
			$home_attendance = $this->Game->_read_attendance($game['Game']['home_team'], Set::extract('/Division/Day/id', $game), $id, null, true);
			usort ($home_attendance['Person'], array('Person', 'comparePerson'));
			$away_attendance = $this->Game->_read_attendance($game['Game']['away_team'], Set::extract('/Division/Day/id', $game), $id, null, true);
			usort ($away_attendance['Person'], array('Person', 'comparePerson'));
		}

		$this->set(compact('game', 'team_id', 'attendance', 'home_attendance', 'away_attendance', 'sport_obj'));
	}

	function _spiritTeams($to, $from, &$data) {
		$data['SpiritEntry'][$to]['created_team_id'] = $from;
		$data['SpiritEntry'][$to]['team_id'] = $to;
	}

	function _spiritMerge($to, $scores, &$data) {
		$data['SpiritEntry'][$to] = array_merge ($data['SpiritEntry'][$to], $scores);
	}

	function _finalize($id) {
		$this->Game->contain (array (
			'GameSlot',
			'Division' => array(
				'Person' => array('fields' => array('id', 'first_name', 'last_name', 'email')),
				'League',
			),
			'ScoreEntry',
			'SpiritEntry',
			// Get the list of captains for each team, we may need to email them
			'HomeTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.role' => Configure::read('privileged_roster_roles')),
					'fields' => array('id', 'first_name', 'last_name', 'email'),
				),
			),
			'AwayTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.role' => Configure::read('privileged_roster_roles')),
					'fields' => array('id', 'first_name', 'last_name', 'email'),
				),
			),
			'ScoreReminderEmail',
			'ScoreMismatchEmail',
		));
		$game = $this->Game->read(null, $id);
		$this->Configuration->loadAffiliate($game['Division']['League']['affiliate_id']);
		$this->Game->_adjustEntryIndices($game);

		if ($this->Game->_is_finalized($game)) {
			return __('Game has already been finalized.', true);
		}

		$result = $this->_finalizeGame ($game);
		if ($result !== true) {
			return $result;
		}
		return true;
	}

	/**
	 * Take what is currently known about the game, and finalize it.
	 * If we have:
	 * 	0) no scores entered
	 * 		- forfeit game as 0-0 tie
	 * 		- give poor spirit to both
	 * 	1) one score entered
	 * 		- use single score as final
	 * 		- give full spirit to entering team, assigned spirit, less
	 * 		  some configurable penalty, to non-entering team.
	 * 	2) two scores entered, not agreeing
	 * 		- send email to the coordinator(s).
	 *  3) two scores entered, agreeing
	 *  	- scores are entered as provided, as are spirit values.
	 */
	function _finalizeGame($game) {
		// Initialize data to be saved
		$data = array('Game' => array('id' => $game['Game']['id'], 'status' => 'normal'));
		$spirit_obj = $this->_getComponent ('Spirit', $game['Division']['League']['sotg_questions'], $this);

		$home_entry = $this->Game->_get_score_entry($game, $game['Game']['home_team']);
		$away_entry = $this->Game->_get_score_entry($game, $game['Game']['away_team']);
		if ($home_entry && $away_entry) {
			if ($this->Game->_score_entries_agree($home_entry, $away_entry)) {
				$data['Game']['status'] = $home_entry['status'];
				if ($home_entry['status'] == 'normal') {
					// No default.  Just finalize score.
					$data['Game']['home_score'] = $home_entry['score_for'];
					$data['Game']['away_score'] = $home_entry['score_against'];
				}
				$data['Game']['approved_by'] = APPROVAL_AUTOMATIC;
			} else {
				// Maybe send a notification email to the convener
				// TODO: Do this on a recurring basis, every few days, instead of just once
				if (empty ($game['ScoreMismatchEmail'])) {
					$this->set(compact ('game'));
					if ($this->_sendMail (array (
							'to' => $game['Division'],
							'subject' => 'Score entry mismatch',
							'template' => 'score_entry_mismatch',
							'sendAs' => 'both',
					)))
					{
						// TODO: Save this directly
						$this->Game->ScoreMismatchEmail->create();
//						$data['ScoreMismatchEmail'][0] = array(
						$this->Game->ScoreMismatchEmail->save(array(
							'type' => 'email_score_mismatch',
							'game_id' => $game['Game']['id'],
						));
					}
				}
				return __('This score doesn\'t agree with the one your opponent submitted. Because of this, the score will not be posted until your coordinator approves it. Alternately, whichever captain made an error can edit their submission.', true);
			}
		} else if ( $home_entry && !$away_entry ) {
			$data['Game']['status'] = $home_entry['status'];
			switch( $home_entry['status'] ) {
				case 'home_default':
					$this->_spiritTeams ($game['Game']['home_team'], $game['Game']['away_team'], $data);
					$this->_spiritMerge ($game['Game']['home_team'], $spirit_obj->defaulted(), $data);
					break;
				case 'away_default':
					$this->_spiritTeams ($game['Game']['home_team'], $game['Game']['away_team'], $data);
					$this->_spiritMerge ($game['Game']['home_team'], $spirit_obj->expected(), $data);
					break;
				case 'normal':
					$data['Game']['home_score'] = $home_entry['score_for'];
					$data['Game']['away_score'] = $home_entry['score_against'];
					$this->_spiritTeams ($game['Game']['home_team'], $game['Game']['away_team'], $data);
					$this->_spiritMerge ($game['Game']['home_team'], $spirit_obj->expected(), $data);
					break;
				default:
					$data['Game']['home_score'] = $data['Game']['away_score'] = null;
					break;
			}
			if (!in_array($home_entry['status'], Configure::read('unplayed_status'))) {
				$penalty = Configure::read('scoring.missing_score_spirit_penalty');
				$data['SpiritEntry'][$game['Game']['away_team']] = array(
					'id' => $game['SpiritEntry'][$game['Game']['away_team']]['id'],
					'entered_sotg' => max (0, $game['SpiritEntry'][$game['Game']['away_team']]['entered_sotg'] - $penalty),
					'score_entry_penalty' => -$penalty,
				);
			}
			$data['Game']['approved_by'] = APPROVAL_AUTOMATIC_HOME;
			$this->_remindTeam($game, $game['AwayTeam'], $game['HomeTeam'], 'score_approval', 'notification of score approval', false);
		} else if ( !$home_entry && $away_entry ) {
			$data['Game']['status'] = $away_entry['status'];
			switch( $away_entry['status'] ) {
				case 'away_default':
					$this->_spiritTeams ($game['Game']['away_team'], $game['Game']['home_team'], $data);
					$this->_spiritMerge ($game['Game']['away_team'], $spirit_obj->defaulted(), $data);
					break;
				case 'home_default':
					$this->_spiritTeams ($game['Game']['away_team'], $game['Game']['home_team'], $data);
					$this->_spiritMerge ($game['Game']['away_team'], $spirit_obj->expected(), $data);
					break;
				case 'normal':
					$data['Game']['home_score'] = $away_entry['score_against'];
					$data['Game']['away_score'] = $away_entry['score_for'];
					$this->_spiritTeams ($game['Game']['away_team'], $game['Game']['home_team'], $data);
					$this->_spiritMerge ($game['Game']['away_team'], $spirit_obj->expected(), $data);
					break;
			}
			if (!in_array($away_entry['status'], Configure::read('unplayed_status'))) {
				$penalty = Configure::read('scoring.missing_score_spirit_penalty');
				$data['SpiritEntry'][$game['Game']['home_team']] = array(
					'id' => $game['SpiritEntry'][$game['Game']['home_team']]['id'],
					'entered_sotg' => max (0, $game['SpiritEntry'][$game['Game']['home_team']]['entered_sotg'] - $penalty),
					'score_entry_penalty' => -$penalty,
				);
			}
			$data['Game']['approved_by'] = APPROVAL_AUTOMATIC_AWAY;
			$this->_remindTeam($game, $game['HomeTeam'], $game['AwayTeam'], 'score_approval', 'notification of score approval', false);
		} else if ( !$home_entry && !$away_entry ) {
			// TODO: don't do automatic forfeit yet.  Make it per-league configurable
			return __('No score entry found for either team; cannot finalize this game.', true);
		}

		// Possibly adjust the score if the game status changed
		$this->_adjustScoreAndRatings($game, $data);

		if (! $this->Game->saveAll($data)) {
			return __('Could not successfully save game results.', true);
		}

		// Delete score entries
		$this->Game->ScoreEntry->deleteAll(array('game_id' => $game['Game']['id']));

		$this->_updateDependencies ($game, $data['Game']['home_score'] > $data['Game']['away_score']);

		$cache_file = CACHE . 'queries' . DS . "division_{$game['Division']['id']}.data";
		if (file_exists($cache_file)) {
			unlink($cache_file);
		}
		$cache_file = CACHE . 'queries' . DS . "schedule_{$game['Division']['id']}.data";
		if (file_exists($cache_file)) {
			unlink($cache_file);
		}

		return true;
	}

	function _updateDependencies($game, $home_win) {
		if ($home_win) {
			$winner = $game['HomeTeam']['id'];
			$loser = $game['AwayTeam']['id'];
		} else {
			$winner = $game['AwayTeam']['id'];
			$loser = $game['HomeTeam']['id'];
		}

		// Look for games with this as a game dependency
		foreach (array('home', 'away') as $type) {
			$games = $this->Game->find ('all', array(
					'conditions' => array(
						"{$type}_dependency_type LIKE" => 'game_%',
						"{$type}_dependency_id" => $game['Game']['id'],
					),
					'contain' => false,
			));
			foreach ($games as $dependency) {
				$this->Game->id = $dependency['Game']['id'];
				if ($dependency['Game']["{$type}_dependency_type"] == 'game_winner') {
					$this->Game->saveField("{$type}_team", $winner);
				}
				if ($dependency['Game']["{$type}_dependency_type"] == 'game_loser') {
					$this->Game->saveField("{$type}_team", $loser);
				}
			}
		}
	}

	function _adjustScoreAndRatings($game, &$data) {
		if ($data['Game']['status'] != $game['Game']['status']) {
			switch ($data['Game']['status']) {
				case 'home_default':
					$data['Game']['home_score'] = Configure::read('scoring.default_losing_score');
					$data['Game']['away_score'] = Configure::read('scoring.default_winning_score');
					break;

				case 'away_default':
					$data['Game']['home_score'] = Configure::read('scoring.default_winning_score');
					$data['Game']['away_score'] = Configure::read('scoring.default_losing_score');
					break;

				case 'normal':
					break;

				default:
					$data['Game']['home_score'] = $data['Game']['away_score'] = null;
					break;
			}
		}

		// Finalize the rating change if we've just updated the score
		if ($data['Game']['home_score'] != $game['Game']['home_score'] || $data['Game']['away_score'] != $game['Game']['away_score']) {
			$this->_modifyTeamRatings($game, $data);

			// If this league has stat tracking, we may need to update some calculated stats
			if (League::hasStats($game)) {
				if (($game['Game']['home_score'] < $game['Game']['away_score'] && $data['Game']['home_score'] >= $data['Game']['away_score']) ||
					($game['Game']['home_score'] > $game['Game']['away_score'] && $data['Game']['home_score'] <= $data['Game']['away_score']) ||
					($game['Game']['home_score'] == $game['Game']['away_score'] && $data['Game']['home_score'] != $data['Game']['away_score']))
				{
					$calc_stats = $this->Game->Division->League->StatType->find('all', array(
							'contain' => array(),
							'conditions' => array(
								'StatType.type' => 'game_calc',
								'StatType.sport' => $game['Division']['League']['sport'],
							),
							'fields' => array('id', 'handler'),
					));
					$sport_obj = $this->_getComponent ('Sport', $game['Division']['League']['sport'], $this);

					// Need to copy a bit of data around in the submitted array to match function expectations
					$data['Game']['home_team'] = $data['HomeTeam']['id'];
					$data['Game']['away_team'] = $data['AwayTeam']['id'];

					foreach ($calc_stats as $stat_type) {
						$func = "{$stat_type['StatType']['handler']}_game_recalc";
						if (method_exists($sport_obj, $func)) {
							$sport_obj->$func($stat_type['StatType'], $data);
						}
					}
				}
			}
		}

		// Any time that this is called, the division seeding might change.
		// We just reset it here, and it will be recalculated as required elsewhere.
		$this->Game->Division->Team->updateAll(array('seed' => 0), array('Team.division_id' => $game['Division']['id']));
	}

	/**
	 * Calculate the value to be added/subtracted from the competing
	 * teams' ratings, using the defined league component.
	 */
	function _modifyTeamRatings ($game, &$data) {
		// Initialize what the home and away team ratings will be after this game is finalized
		// We also need to set the team ids in the data to be saved
		$data['HomeTeam'] = array(
			'id' => $game['HomeTeam']['id'],
			'rating' => $game['HomeTeam']['rating'],
		);
		$data['AwayTeam'] = array(
			'id' => $game['AwayTeam']['id'],
			'rating' => $game['AwayTeam']['rating'],
		);

		// If we already have a rating, reverse the effect of this game from the
		// team ratings, and recalculate it.
		if (!is_null($game['Game']['rating_points']) && $game['Game']['rating_points'] != 0) {
			if ($game['Game']['home_score'] >= $game['Game']['away_score']) {
				$data['HomeTeam']['rating'] -= $game['Game']['rating_points'];
				$data['AwayTeam']['rating'] += $game['Game']['rating_points'];
			} else if($game['Game']['away_score'] > $game['Game']['home_score']) {
				$data['HomeTeam']['rating'] += $game['Game']['rating_points'];
				$data['AwayTeam']['rating'] -= $game['Game']['rating_points'];
			}
		}

		// If we're not a normal game, avoid changing the rating.
		$change_rating = false;
		if ($data['Game']['status'] == 'normal') {
			$change_rating = true;
		}
		if (Configure::read('scoring.default_transfer_ratings') &&
			($data['Game']['status'] == 'home_default' || $data['Game']['status'] == 'away_default') )
		{
			$change_rating = true;
		}
		if ($game['Game']['type'] != SEASON_GAME) {
			$change_rating = false;
		}

		if (! $change_rating) {
			$data['Game']['rating_points'] = 0;
			return true;
		}

		$change = 0;
		$ratings_obj = $this->_getComponent ('Ratings', $game['Division']['rating_calculator'], $this);

		// For a tie, we assume the home team wins
		if ($data['Game']['home_score'] >= $data['Game']['away_score']) {
			$change = $ratings_obj->calculateRatingsChange($data['Game']['home_score'], $data['Game']['away_score'],
					$ratings_obj->calculateExpectedWin($data['HomeTeam']['rating'], $data['AwayTeam']['rating']));
			$data['HomeTeam']['rating'] += $change;
			$data['AwayTeam']['rating'] -= $change;
		} else {
			$change = $ratings_obj->calculateRatingsChange($data['Game']['home_score'], $data['Game']['away_score'],
					$ratings_obj->calculateExpectedWin($data['AwayTeam']['rating'], $data['HomeTeam']['rating']));
			$data['HomeTeam']['rating'] -= $change;
			$data['AwayTeam']['rating'] += $change;
		}

		$data['Game']['rating_points'] = $change;

		return true;
	}

	function _remindTeam($game, $team, $opponent, $template, $subject, $update_db) {
		if (array_key_exists($team['id'], $game['ScoreEntry'])) {
			return false;
		}

		if ($update_db) {
			if (array_key_exists ($team['id'], $game['ScoreReminderEmail'])) {
				return false;
			}
		}

		$this->set(array(
				'team' => $team,
				'opponent' => $opponent,
				'division' => $game['Division'],
				'game' => $game,
				'captains' => implode(', ', Set::extract('/Person/first_name', $team)),
		));

		if (!$this->_sendMail (array (
				'to' => $team,
				'replyTo' => $game['Division']['Person'],
				'subject' => "{$team['name']} $subject",
				'template' => $template,
				'sendAs' => 'both',
		)))
		{
			return false;
		}

		if ($update_db) {
			$this->Game->ScoreReminderEmail->create();
			$this->Game->ScoreReminderEmail->save(array(
				'type' => "email_$template",
				'game_id' => $game['Game']['id'],
				'team_id' => $team['id'],
			));
		}
		return true;
	}

	function stats() {
		$id = $this->_arg('game');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$team_id = $this->_arg('team');
		if ($team_id) {
			$stat_conditions = array('Stat.team_id' => $team_id);
		} else {
			$stat_conditions = array();
		}
		$this->Game->contain(array(
			'Division' => array(
				'League' => array('StatType' => array('conditions' => array('StatType.type' => Configure::read('stat_types.game')))),
			),
			'HomeTeam',
			'AwayTeam',
			'GameSlot' => array('Field' => 'Facility'),
			'ScoreEntry',
			'Stat' => array('conditions' => $stat_conditions),
		));
		$game = $this->Game->read(null, $id);
		if (!$game) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('game', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		if (!League::hasStats($game['Division']['League'])) {
			$this->Session->setFlash(__('This league does not have stat tracking enabled.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'game' => $id));
		}

		if ("{$game['GameSlot']['game_date']} {$game['GameSlot']['game_start']}" > date('Y-m-d H:i:s')) {
			$this->Session->setFlash(__('This game has not yet started.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'game' => $id));
		}

		if (empty($game['Stat'])) {
			$this->Session->setFlash(__('No stats have been entered for this game.', true), 'default', array('class' => 'info'));
			// Redirect coordinators to the stats entry page with whatever parameters were used here
			if (in_array($game['Division']['id'], $this->Session->read('Zuluru.DivisionIDs'))) {
				$this->redirect(array('action' => 'submit_stats', 'game' => $id, 'team' => $team_id));
			}
			// If there was no team ID given, check if one of the two teams is captained by the current user
			if (!$team_id) {
				$teams = array_intersect (array($game['Game']['home_team'], $game['Game']['away_team']), $this->Session->read('Zuluru.OwnedTeamIDs'));
				$team_id = array_pop ($teams);
			}
			// If we have a team ID and we're a captain of that team, go to the stats entry page
			if ($team_id && in_array($team_id, $this->Session->read('Zuluru.OwnedTeamIDs'))) {
				$this->redirect(array('action' => 'submit_stats', 'game' => $id, 'team' => $team_id));
			}
			$this->redirect(array('action' => 'view', 'game' => $id));
		}

		$this->Configuration->loadAffiliate($game['Division']['League']['affiliate_id']);
		$sport_obj = $this->_getComponent ('Sport', $game['Division']['League']['sport'], $this);

		// Team rosters may have changed since the game was played, so use the list of people with stats instead
		foreach (array('HomeTeam', 'AwayTeam') as $key) {
			$people = array_unique(Set::extract("/Stat[team_id={$game[$key]['id']}]/person_id", $game));
			$game[$key]['Person'] = $this->Game->HomeTeam->Person->find('all', array(
					'contain' => array(),
					'conditions' => array('Person.id' => $people),
			));
			usort ($game[$key]['Person'], array('Person', 'comparePerson'));
		}

		if ($game['Game']['home_team'] == $team_id || $team_id === null) {
			$team = $game['HomeTeam'];
			$opponent = $game['AwayTeam'];
		} else if ($game['Game']['away_team'] == $team_id) {
			$team = $game['AwayTeam'];
			$opponent = $game['HomeTeam'];
		} else {
			$this->Session->setFlash(__('That team is not playing in this game.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'game' => $id));
		}

		$this->set(compact('game', 'team_id', 'team', 'opponent', 'sport_obj'));

		if ($this->params['url']['ext'] == 'csv') {
			$this->set('download_file_name', "Stats - Game {$game['Game']['id']}");
			Configure::write ('debug', 0);
		}
	}

	function past() {
		$team_ids = $this->Session->read('Zuluru.TeamIDs');
		if (empty ($team_ids)) {
			return array();
		}

		return array_reverse ($this->Game->find ('all', array(
			'limit' => 4,
			'conditions' => array(
				'OR' => array(
					'HomeTeam.id' => $team_ids,
					'AwayTeam.id' => $team_ids,
				),
				'Game.published' => true,
				'GameSlot.game_date < CURDATE()',
			),
			'fields' => array(
				'Game.id', 'Game.home_team', 'Game.home_score', 'Game.away_team', 'Game.away_score', 'Game.status', 'Game.division_id',
				'HomePoolTeam.dependency_type', 'HomePoolTeam.dependency_id', 'AwayPoolTeam.dependency_type', 'AwayPoolTeam.dependency_id',
				'GameSlot.game_date', 'GameSlot.game_start', 'GameSlot.game_end',
				'HomeTeam.id', 'HomeTeam.name',
				'AwayTeam.id', 'AwayTeam.name',
			),
			'contain' => array(
				'Division' => array('Day', 'League'),
				'GameSlot' => array('Field' => 'Facility'),
				'ScoreEntry' => array('conditions' => array('ScoreEntry.team_id' => $team_ids)),
				'HomeTeam',
				'HomePoolTeam' => 'DependencyPool',
				'AwayTeam',
				'AwayPoolTeam' => 'DependencyPool',
				'Attendance' => array(
					'conditions' => array('Attendance.person_id' => $this->Auth->user('id')),
				),
			),
			'order' => 'GameSlot.game_date DESC, GameSlot.game_start DESC',
		)));
	}

	function future($recursive = false) {
		$team_ids = $this->Session->read('Zuluru.TeamIDs');
		if (empty ($team_ids)) {
			return array();
		}

		$games = $this->Game->find ('all', array(
			'limit' => 4,
			'conditions' => array(
				'OR' => array(
					'HomeTeam.id' => $team_ids,
					'AwayTeam.id' => $team_ids,
				),
				'Game.published' => true,
				'GameSlot.game_date >= CURDATE()',
			),
			'fields' => array(
				'Game.id', 'Game.home_team', 'Game.home_score', 'Game.away_team', 'Game.away_score', 'Game.status', 'Game.division_id',
				'HomePoolTeam.dependency_type', 'HomePoolTeam.dependency_id', 'AwayPoolTeam.dependency_type', 'AwayPoolTeam.dependency_id',
				'GameSlot.game_date', 'GameSlot.game_start', 'GameSlot.game_end',
				'HomeTeam.id', 'HomeTeam.name',
				'AwayTeam.id', 'AwayTeam.name',
			),
			'contain' => array(
				'Division' => array('Day', 'League'),
				'GameSlot' => array('Field' => 'Facility'),
				'ScoreEntry' => array('conditions' => array('ScoreEntry.team_id' => $team_ids)),
				'HomeTeam',
				'HomePoolTeam' => 'DependencyPool',
				'AwayTeam',
				'AwayPoolTeam' => 'DependencyPool',
				'Attendance' => array(
					'conditions' => array('Attendance.person_id' => $this->Auth->user('id')),
				),
			),
			'order' => 'GameSlot.game_date ASC, GameSlot.game_start ASC',
		));

		// Check if we need to update attendance records for any upcoming games
		$reread = false;
		foreach ($games as $game) {
			if (empty ($game['Attendance'])) {
				if (!empty($game['HomeTeam']['id']) && $game['HomeTeam']['track_attendance'] && in_array($game['HomeTeam']['id'], $team_ids)) {
					$attendance = $this->Game->_read_attendance($game['HomeTeam']['id'], Set::extract('/Division/Day/id', $game), $game['Game']['id']);
					$reread = true;
				}
				if (!empty($game['AwayTeam']['id']) && $game['AwayTeam']['track_attendance'] && in_array($game['AwayTeam']['id'], $team_ids)) {
					$attendance = $this->Game->_read_attendance($game['AwayTeam']['id'], Set::extract('/Division/Day/id', $game), $game['Game']['id']);
					$reread = true;
				}
			}
		}

		if ($reread && !$recursive) {
			return $this->future(true);
		}
		return $games;
	}

	function cron() {
		$this->layout = 'bare';

		if (!$this->Lock->lock ('cron')) {
			return false;
		}

		$this->Game->contain (array (
			'GameSlot',
			'Division' => array(
				'Person' => array('fields' => array('id', 'first_name', 'last_name', 'email')),
				'League',
			),
			'ScoreEntry',
			'SpiritEntry',
			// Get the list of captains for each team, we may need to email them
			'HomeTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.role' => Configure::read('privileged_roster_roles')),
					'fields' => array('id', 'first_name', 'last_name', 'email'),
				),
			),
			'AwayTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.role' => Configure::read('privileged_roster_roles')),
					'fields' => array('id', 'first_name', 'last_name', 'email'),
				),
			),
			'ScoreReminderEmail',
			'ScoreMismatchEmail',
		));
		$offset = Configure::read('timezone.adjust') * 60;
		$games = $this->Game->find ('all', array(
				'conditions' => array(
					'Division.is_open' => true,
					'Game.published' => true,
					"UNIX_TIMESTAMP(CONCAT_WS(' ', GameSlot.game_date, GameSlot.game_start)) + $offset + Division.email_after * 60 * 60 < UNIX_TIMESTAMP(NOW())",
					array('OR' => array(
						'Game.home_score' => null,
						'Game.away_score' => null,
					)),
					'NOT' => array('Game.status' => Configure::read('unplayed_status')),
					array('OR' => array(
						'Division.email_after >' => 0,
						'Division.finalize_after >' => 0,
					)),
				),
				'order' => array('Division.id', 'GameSlot.game_date', 'GameSlot.game_start', 'Game.id'),
		));

		$this->Game->_adjustEntryIndices($games);
		$now = time();
		foreach ($games as $key => $game) {
			$game_time = strtotime ("{$game['GameSlot']['game_date']} {$game['GameSlot']['game_start']}");
			$email_time = $game_time + $offset + $game['Division']['email_after'] * 60 * 60;
			$finalize_time = $game_time + $offset + $game['Division']['finalize_after'] * 60 * 60;
			$games[$key]['finalized'] = $games[$key]['emailed'] = false;
			if ($game['Division']['finalize_after'] > 0 && $now > $finalize_time) {
				$games[$key]['finalized'] = true;
				$games[$key]['finalized'] = $this->_finalizeGame ($game);
			}
			if ($game['Division']['email_after'] > 0 && $games[$key]['finalized'] !== true && $now > $email_time) {
				$games[$key]['emailed'] = $this->_remindTeam($game, $game['HomeTeam'], $game['AwayTeam'], 'score_reminder', 'reminder to submit score', true);
				$games[$key]['emailed'] = $this->_remindTeam($game, $game['AwayTeam'], $game['HomeTeam'], 'score_reminder', 'reminder to submit score', true) || $games[$key]['emailed'];
			}
		}

		// This "days" calculation isn't precise, as it doesn't handle leap years.
		// However, it's close enough since we're never looking at periods that span
		// from a year end to a leap day.
		$days = date('Y') * 365 + date('z');

		// Find all of the games that might have players that need to be reminded about attendance
		// TODO: Do we need to do something to handle games that aren't yet scheduled?
		$this->Game->contain(array(
			'Division' => array('Day'),
			'GameSlot' => array('Field' => 'Facility'),
			'HomeTeam',
			'AwayTeam',
			'AttendanceReminderEmail',
		));
		$remind = $this->Game->find ('all', array(
				'conditions' => array(
					'Game.published' => true,
					'GameSlot.game_date >= CURDATE()',
					'OR' => array(
						// DATEDIFF might be a better way to do this, but it's less standardized
						array(
							'HomeTeam.track_attendance' => true,
							'HomeTeam.attendance_reminder !=' => -1,
							'DATE_ADD(CURDATE(), INTERVAL HomeTeam.attendance_reminder DAY) >= GameSlot.game_date',
						),
						array(
							'AwayTeam.track_attendance' => true,
							'AwayTeam.attendance_reminder !=' => -1,
							'DATE_ADD(CURDATE(), INTERVAL AwayTeam.attendance_reminder DAY) >= GameSlot.game_date',
						),
					),
				),
		));

		$remind_count = 0;
		foreach ($remind as $game) {
			$game_date = strtotime($game['GameSlot']['game_date']);
			$days_to_game = date('Y', $game_date) * 365 + date('z', $game_date) - $days;
			$reminded = Set::extract('/AttendanceReminderEmail/person_id', $game);

			if ($game['HomeTeam']['track_attendance'] && $game['HomeTeam']['attendance_reminder'] >= $days_to_game) {
				$remind_count += $this->_remindAttendance($game, $game['HomeTeam'], $game['AwayTeam'], $reminded);
			}
			if ($game['AwayTeam']['track_attendance'] && $game['AwayTeam']['attendance_reminder'] >= $days_to_game) {
				$remind_count += $this->_remindAttendance($game, $game['AwayTeam'], $game['HomeTeam'], $reminded);
			}
		}

		// Find all of the games that might have captains that need attendance summaries
		// TODO: Do we need to do something to handle games that aren't yet scheduled?
		$this->Game->contain(array(
			'Division' => array('Day'),
			'GameSlot' => array('Field' => 'Facility'),
			// Get the list of captains for each team, we may need to email them
			'HomeTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.role' => Configure::read('privileged_roster_roles')),
					'fields' => array('id', 'first_name', 'last_name', 'email'),
				),
			),
			'AwayTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.role' => Configure::read('privileged_roster_roles')),
					'fields' => array('id', 'first_name', 'last_name', 'email'),
				),
			),
			'AttendanceSummaryEmail',
		));
		$summary = $this->Game->find ('all', array(
				'conditions' => array(
					'Game.published' => true,
					'GameSlot.game_date >= CURDATE()',
					'OR' => array(
						// DATEDIFF might be a better way to do this, but it's less standardized
						array(
							'HomeTeam.track_attendance' => true,
							'HomeTeam.attendance_summary !=' => -1,
							'DATE_ADD(CURDATE(), INTERVAL HomeTeam.attendance_summary DAY) >= GameSlot.game_date',
						),
						array(
							'AwayTeam.track_attendance' => true,
							'AwayTeam.attendance_summary !=' => -1,
							'DATE_ADD(CURDATE(), INTERVAL AwayTeam.attendance_summary DAY) >= GameSlot.game_date',
						),
					),
				),
		));

		$summary_count = 0;
		foreach ($summary as $game) {
			$game_date = strtotime($game['GameSlot']['game_date']);
			$days_to_game = date('Y', $game_date) * 365 + date('z', $game_date) - $days;
			$summarized = Set::extract('/AttendanceSummaryEmail/team_id', $game);

			if ($game['HomeTeam']['track_attendance'] && $game['HomeTeam']['attendance_summary'] >= $days_to_game) {
				$summary_count += $this->_summarizeAttendance($game, $game['HomeTeam'], $game['AwayTeam'], $summarized);
			}
			if ($game['AwayTeam']['track_attendance'] && $game['AwayTeam']['attendance_summary'] >= $days_to_game) {
				$summary_count += $this->_summarizeAttendance($game, $game['AwayTeam'], $game['HomeTeam'], $summarized);
			}
		}

		$this->set(compact('games', 'remind_count', 'summary_count'));

		$this->Lock->unlock();
	}

	function _remindAttendance($game, $team, $opponent, $reminded) {
		$this->set(compact ('game', 'team', 'opponent'));

		// Read the attendance records for this game and team.
		// We have to do it this way, not as a contain on the main find,
		// so that any missing records are created for us.
		$attendance = $this->Game->_read_attendance($team['id'], Set::extract('/Division/Day/id', $game), $game['Game']['id']);
		$sent = 0;
		foreach ($attendance['Person'] as $person) {
			$regular = in_array($person['TeamsPerson']['role'], Configure::read('playing_roster_roles'));
			$sub = (!$regular && in_array($person['TeamsPerson']['role'], Configure::read('extended_playing_roster_roles')));
			$always = (!empty($person['Setting']) && $person['Setting'][0]['value'] != false);
			if (!is_array($reminded) || !in_array($person['id'], $reminded)) {
				if (($regular && $person['Attendance'][0]['status'] == ATTENDANCE_UNKNOWN) ||
					($sub && $person['Attendance'][0]['status'] == ATTENDANCE_INVITED) ||
					$always)
				{
					$this->set(compact ('person'));
					$this->set('status', $person['Attendance'][0]['status']);
					$this->set('code', $this->_hash ($person['Attendance'][0]));

					if ($this->_sendMail (array (
							'to' => $person,
							// Attendance array is sorted by role, so the first one is the captain
							'replyTo' => $attendance['Person'][0],
							'subject' => "{$team['name']} attendance reminder",
							'template' => 'attendance_reminder',
							'sendAs' => 'both',
					)))
					{
						++$sent;
						$this->Game->AttendanceReminderEmail->create();
						$this->Game->AttendanceReminderEmail->save(array(
							'type' => 'email_attendance_reminder',
							'game_id' => $game['Game']['id'],
							'person_id' => $person['id'],
						));
					}
				}
			}
		}

		return $sent;
	}

	function _summarizeAttendance($game, $team, $opponent, $summarized) {
		if (is_array($summarized) && in_array($team['id'], $summarized)) {
			return;
		}

		$this->set(compact ('game', 'team', 'opponent'));

		// Read the attendance records for this game and team.
		// We have to do it this way, not as a contain on the main find,
		// so that any missing records are created for us.
		$attendance = $this->Game->_read_attendance($team['id'], Set::extract('/Division/Day/id', $game), $game['Game']['id']);

		// Summarize by attendance status
		$summary = array_fill_keys(array_keys(Configure::read('attendance')),
				array_fill_keys(array_keys(Configure::read('options.gender')), array())
		);
		foreach ($attendance['Person'] as $person) {
			$summary[$person['Attendance'][0]['status']][$person['gender']][] = $person['full_name'];
		}
		$this->set(compact ('summary'));

		$this->set('captains', implode (', ', Set::extract ('/Person/first_name', $team)));
		if ($this->_sendMail (array (
				'to' => $team['Person'],
				'subject' => "{$team['name']} attendance summary",
				'template' => 'attendance_summary',
				'sendAs' => 'both',
		)))
		{
			$this->Game->AttendanceSummaryEmail->create();
			$this->Game->AttendanceSummaryEmail->save(array(
				'type' => 'email_attendance_summary',
				'game_id' => $game['Game']['id'],
				'team_id' => $team['id'],
			));
			return 1;
		}
		return 0;
	}

	function _hash ($attendance, $salt = true) {
		// Build a string of the inputs
		$input = "{$attendance['id']}:{$attendance['team_id']}:{$attendance['game_id']}:{$attendance['person_id']}:{$attendance['created']}";
		if (array_key_exists ('captain', $attendance)) {
			$input .= ":captain";
		}
		if ($salt) {
			$input = $input . ':' . Configure::read('Security.salt');
		}
		return md5($input);
	}
}
?>
