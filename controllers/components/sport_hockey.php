<?php
/**
 * Class for Hockey sport-specific functionality.
 */

class SportHockeyComponent extends SportComponent
{
	var $sport = 'hockey';

	function points_game($stat_type, $game, &$stats) {
		$this->_game_sum($stat_type, $game, $stats, array('Goals', 'Assists'));
	}

	function shot_percent_game($stat_type, $game, &$stats) {
		$this->_game_percent($stat_type, $game, $stats, $this->_stat_type_id('Goals'), $this->_stat_type_id('Shots'));
	}

	function shot_percent_season($stat_type, &$stats) {
		$this->_season_percent($stat_type, $stats, $this->_stat_type_id('Goals'), $this->_stat_type_id('Shots'));
	}

	function faceoff_percent_game($stat_type, $game, &$stats) {
		$this->_game_percent($stat_type, $game, $stats, $this->_stat_type_id('Faceoffs Won'), $this->_stat_type_id('Faceoffs'));
	}

	function faceoff_percent_season($stat_type, &$stats) {
		$this->_season_percent($stat_type, $stats, $this->_stat_type_id('Faceoffs Won'), $this->_stat_type_id('Faceoffs'));
	}

	function shutouts_game($stat_type, $game, &$stats) {
		$this->_init_rosters($stats);

		$m_id = $this->_stat_type_id('Minutes Played');
		$evg_id = $this->_stat_type_id('Even Strength Goals Against');
		$ppg_id = $this->_stat_type_id('Power Play Goals Against');
		$shg_id = $this->_stat_type_id('Shorthanded Goals Against');
		$eng_id = $this->_stat_type_id('Empty Net Goals Against');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$minutes = $this->_value($m_id, $person_id, $stats);
				if ($minutes) {
					$goals = $this->_value($evg_id, $person_id, $stats) + $this->_value($ppg_id, $person_id, $stats) + $this->_value($shg_id, $person_id, $stats) + $this->_value($eng_id, $person_id, $stats);
					$value = ($goals == 0 ? 1 : 0);
				} else {
					$value = 0;
				}

				if (Stat::applicable($stat_type, $position) || $value != 0) {
					$stats['Stat'][] = array(
						'game_id' => $game['Game']['id'],
						'team_id' => $team_id,
						'person_id' => $person_id,
						'stat_type_id' => $stat_type['id'],
						'value' => $value,
					);
				}
			}
		}
	}

	function goals_against_game($stat_type, $game, &$stats) {
		$this->_init_rosters($stats);

		$evg_id = $this->_stat_type_id('Even Strength Goals Against');
		$ppg_id = $this->_stat_type_id('Power Play Goals Against');
		$shg_id = $this->_stat_type_id('Shorthanded Goals Against');
		$eng_id = $this->_stat_type_id('Empty Net Goals Against');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->_value($evg_id, $person_id, $stats) + $this->_value($ppg_id, $person_id, $stats) + $this->_value($shg_id, $person_id, $stats) + $this->_value($eng_id, $person_id, $stats);

				if (Stat::applicable($stat_type, $position) || $value != 0) {
					$stats['Stat'][] = array(
						'game_id' => $game['Game']['id'],
						'team_id' => $team_id,
						'person_id' => $person_id,
						'stat_type_id' => $stat_type['id'],
						'value' => $value,
					);
				}
			}
		}
	}

	function save_percent_game($stat_type, $game, &$stats) {
		$this->_init_rosters($stats);

		$s_id = $this->_stat_type_id('Shots Against');
		$evg_id = $this->_stat_type_id('Even Strength Goals Against');
		$ppg_id = $this->_stat_type_id('Power Play Goals Against');
		$shg_id = $this->_stat_type_id('Shorthanded Goals Against');
		$eng_id = $this->_stat_type_id('Empty Net Goals Against');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$shots = $this->_value($s_id, $person_id, $stats);
				if ($shots) {
					$goals = $this->_value($evg_id, $person_id, $stats) + $this->_value($ppg_id, $person_id, $stats) + $this->_value($shg_id, $person_id, $stats) + $this->_value($eng_id, $person_id, $stats);
					$value = round(($shots - $goals) / $shots, 3);
				} else {
					$value = 0;
				}

				if (Stat::applicable($stat_type, $position) || $value != 0) {
					$stats['Stat'][] = array(
						'game_id' => $game['Game']['id'],
						'team_id' => $team_id,
						'person_id' => $person_id,
						'stat_type_id' => $stat_type['id'],
						'value' => $value,
					);
				}
			}
		}
	}

	function save_percent_season($stat_type, &$stats) {
		$this->_init_rosters($stats);

		$s_id = $this->_stat_type_id('Shots Against');
		$g_id = $this->_stat_type_id('Goals Against');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$shots = $this->_value_sum($s_id, $person_id, $stats);
				if ($shots) {
					$value = round(($shots - $this->_value_sum($g_id, $person_id, $stats)) / $shots, 3);
				} else {
					$value = 0;
				}

				if (Stat::applicable($stat_type, $position) || $value != 0) {
					$stats['Calculated'][$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

	function gaa_season($stat_type, &$stats) {
		$this->_init_rosters($stats);

		$m_id = $this->_stat_type_id('Minutes Played');
		$g_id = $this->_stat_type_id('Goals Against');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$minutes = $this->_value_sum($m_id, $person_id, $stats);
				if ($minutes) {
					$value = round(($this->_value_sum($g_id, $person_id, $stats) * 60) / $minutes, 2);
				} else {
					$value = 0;
				}

				if (Stat::applicable($stat_type, $position) || $value != 0) {
					$stats['Calculated'][$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}
}

?>
