<?php
/**
 * Class for Ultimate sport-specific functionality.
 */

class SportUltimateComponent extends SportComponent
{
	var $sport = 'ultimate';

	function validate_play($team, $play, $score_from, $details) {
		switch ($play) {
			case 'Half':
				$half = Set::extract('/X[play=Half]/.', array('X' => $details));
				if (!empty($half)) {
					return __('Second half was already started.', true);
				}
				$start = Set::extract('/X[play=Start]/.', array('X' => $details));
				if (empty($start)) {
					return __('This game apparently hasn\'t started yet.', true);
				}
				if ($start[0]['team_id'] == $team) {
					return __('The same team shouldn\'t pull to start both halves.', true);
				}
				break;
		}
		return parent::validate_play($team, $play, $score_from, $details);
	}

	function points_game($stat_type, $game, &$stats) {
		$this->_game_sum($stat_type, $game, $stats, array('Goals', 'Assists', 'Second Assists'));
	}

	function turnovers_game($stat_type, $game, &$stats) {
		$this->_game_sum($stat_type, $game, $stats, array('Throwaways', 'Drops'));

	}
}

?>
