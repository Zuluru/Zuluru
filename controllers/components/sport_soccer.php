<?php
/**
 * Class for Soccer sport-specific functionality.
 */

class SportSoccerComponent extends SportComponent
{
	var $sport = 'soccer';

	// In soccer, a win is worth 3 points, not 2.
	function winValue() {
		return 3;
	}

	function points_game($stat_type, $game, &$stats) {
		$this->_init_rosters($stats);

		$g_id = $this->_stat_type_id('Goals');
		$a_id = $this->_stat_type_id('Assists');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->_value($g_id, $person_id, $stats) * 2 + $this->_value($a_id, $person_id, $stats);

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

	function shot_percent_game($stat_type, $game, &$stats) {
		$this->_game_percent($stat_type, $game, $stats, $this->_stat_type_id('Goals'), $this->_stat_type_id('Shots'));
	}

	function shot_percent_season($stat_type, &$stats) {
		$this->_season_percent($stat_type, $stats, $this->_stat_type_id('Goals'), $this->_stat_type_id('Shots'));
	}

	function save_percent_game($stat_type, $game, &$stats) {
		$this->_init_rosters($stats);

		$s_id = $this->_stat_type_id('Shots Against');
		$g_id = $this->_stat_type_id('Goals Against');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$shots = $this->_value($s_id, $person_id, $stats);
				if ($shots) {
					$value = round(($shots - $this->_value($g_id, $person_id, $stats)) / $shots, 3);
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
					$value = round(($this->_value_sum($g_id, $person_id, $stats) * 80) / $minutes, 2);
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
