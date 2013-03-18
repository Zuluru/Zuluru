<?php
/**
 * Class for Basketball sport-specific functionality.
 */

class SportBasketballComponent extends SportComponent
{
	var $sport = 'basketball';

	function points_game($stat_type, $game, &$stats) {
		$this->_init_rosters($stats);

		$fg_id = $this->_stat_type_id('Field Goals Made');
		$ft_id = $this->_stat_type_id('Free Throws Made');
		$tpfg_id = $this->_stat_type_id('Three-point Field Goals Made');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->_value($fg_id, $person_id, $stats) * 2 + $this->_value($ft_id, $person_id, $stats) + $this->_value($tpfg_id, $person_id, $stats) * 3;

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

	function rebounds_game($stat_type, $game, &$stats) {
		$this->_game_sum($stat_type, $game, $stats, array('Offensive Rebounds', 'Defensive Rebounds'));
	}

	function fg_percent_game($stat_type, $game, &$stats) {
		$this->_game_percent($stat_type, $game, $stats, $this->_stat_type_id('Field Goals Made'), $this->_stat_type_id('Field Goals Attempted'));
	}

	function fg_percent_season($stat_type, &$stats) {
		$this->_season_percent($stat_type, $stats, $this->_stat_type_id('Field Goals Made'), $this->_stat_type_id('Field Goals Attempted'));
	}

	function ft_percent_game($stat_type, $game, &$stats) {
		$this->_game_percent($stat_type, $game, $stats, $this->_stat_type_id('Free Throws Made'), $this->_stat_type_id('Free Throws Attempted'));
	}

	function ft_percent_season($stat_type, &$stats) {
		$this->_season_percent($stat_type, $stats, $this->_stat_type_id('Free Throws Made'), $this->_stat_type_id('Free Throws Attempted'));
	}

	function tpfg_percent_game($stat_type, $game, &$stats) {
		$this->_game_percent($stat_type, $game, $stats, $this->_stat_type_id('Three-point Field Goals Made'), $this->_stat_type_id('Three-point Field Goals Attempted'));
	}

	function tpfg_percent_season($stat_type, &$stats) {
		$this->_season_percent($stat_type, $stats, $this->_stat_type_id('Three-point Field Goals Made'), $this->_stat_type_id('Three-point Field Goals Attempted'));
	}

	function astto_game($stat_type, $game, &$stats) {
		$this->_game_ratio($stat_type, $game, $stats, $this->_stat_type_id('Assists'), $this->_stat_type_id('Turnovers'));
	}

	function astto_season($stat_type, &$stats) {
		$this->_season_ratio($stat_type, $stats, $this->_stat_type_id('Assists'), $this->_stat_type_id('Turnovers'));
	}

	function efficiency_game($stat_type, $game, &$stats) {
		$p_id = $this->_stat_type_id('Points');
		$r_id = $this->_stat_type_id('Rebounds');
		$a_id = $this->_stat_type_id('Assists');
		$s_id = $this->_stat_type_id('Steals');
		$b_id = $this->_stat_type_id('Blocks');
		$fgm_id = $this->_stat_type_id('Field Goals Made');
		$fga_id = $this->_stat_type_id('Field Goals Attempted');
		$ftm_id = $this->_stat_type_id('Free Throws Made');
		$fta_id = $this->_stat_type_id('Free Throws Attempted');
		$tpfgm_id = $this->_stat_type_id('Three-point Field Goals Made');
		$tpfga_id = $this->_stat_type_id('Three-point Field Goals Attempted');
		$t_id = $this->_stat_type_id('Turnovers');

		$this->_init_rosters($stats);
		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->_value($p_id, $person_id, $stats)
					+ $this->_value($r_id, $person_id, $stats)
					+ $this->_value($a_id, $person_id, $stats)
					+ $this->_value($s_id, $person_id, $stats)
					+ $this->_value($b_id, $person_id, $stats)
					+ $this->_value($fgm_id, $person_id, $stats) - $this->_value($fga_id, $person_id, $stats)
					+ $this->_value($ftm_id, $person_id, $stats) - $this->_value($fta_id, $person_id, $stats)
					+ $this->_value($tpfgm_id, $person_id, $stats) - $this->_value($tpfga_id, $person_id, $stats)
					- $this->_value($t_id, $person_id, $stats);
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

	function efficiency_season($stat_type, &$stats) {
		$p_id = $this->_stat_type_id('Points');
		$r_id = $this->_stat_type_id('Rebounds');
		$a_id = $this->_stat_type_id('Assists');
		$s_id = $this->_stat_type_id('Steals');
		$b_id = $this->_stat_type_id('Blocks');
		$fgm_id = $this->_stat_type_id('Field Goals Made');
		$fga_id = $this->_stat_type_id('Field Goals Attempted');
		$ftm_id = $this->_stat_type_id('Free Throws Made');
		$fta_id = $this->_stat_type_id('Free Throws Attempted');
		$tpfgm_id = $this->_stat_type_id('Three-point Field Goals Made');
		$tpfga_id = $this->_stat_type_id('Three-point Field Goals Attempted');
		$t_id = $this->_stat_type_id('Turnovers');

		$this->_init_rosters($stats);
		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->_value_sum($p_id, $person_id, $stats)
					+ $this->_value_sum($r_id, $person_id, $stats)
					+ $this->_value_sum($a_id, $person_id, $stats)
					+ $this->_value_sum($s_id, $person_id, $stats)
					+ $this->_value_sum($b_id, $person_id, $stats)
					+ $this->_value_sum($fgm_id, $person_id, $stats) - $this->_value_sum($fga_id, $person_id, $stats)
					+ $this->_value_sum($ftm_id, $person_id, $stats) - $this->_value_sum($fta_id, $person_id, $stats)
					+ $this->_value_sum($tpfgm_id, $person_id, $stats) - $this->_value_sum($tpfga_id, $person_id, $stats)
					- $this->_value_sum($t_id, $person_id, $stats);
				if (Stat::applicable($stat_type, $position) || $value != 0) {
					$stats['Calculated'][$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

	function pir_game($stat_type, $game, &$stats) {
		$p_id = $this->_stat_type_id('Points');
		$r_id = $this->_stat_type_id('Rebounds');
		$a_id = $this->_stat_type_id('Assists');
		$s_id = $this->_stat_type_id('Steals');
		$b_id = $this->_stat_type_id('Blocks');
		$fd_id = $this->_stat_type_id('Fouls Drawn');
		$fgm_id = $this->_stat_type_id('Field Goals Made');
		$fga_id = $this->_stat_type_id('Field Goals Attempted');
		$ftm_id = $this->_stat_type_id('Free Throws Made');
		$fta_id = $this->_stat_type_id('Free Throws Attempted');
		$tpfgm_id = $this->_stat_type_id('Three-point Field Goals Made');
		$tpfga_id = $this->_stat_type_id('Three-point Field Goals Attempted');
		$t_id = $this->_stat_type_id('Turnovers');
		$pf_id = $this->_stat_type_id('Personal Fouls');
		$sr_id = $this->_stat_type_id('Shots Rejected');

		$this->_init_rosters($stats);
		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->_value($p_id, $person_id, $stats)
					+ $this->_value($r_id, $person_id, $stats)
					+ $this->_value($a_id, $person_id, $stats)
					+ $this->_value($s_id, $person_id, $stats)
					+ $this->_value($b_id, $person_id, $stats)
					+ $this->_value($fd_id, $person_id, $stats)
					+ $this->_value($fgm_id, $person_id, $stats) - $this->_value($fga_id, $person_id, $stats)
					+ $this->_value($ftm_id, $person_id, $stats) - $this->_value($fta_id, $person_id, $stats)
					+ $this->_value($tpfgm_id, $person_id, $stats) - $this->_value($tpfga_id, $person_id, $stats)
					- $this->_value($t_id, $person_id, $stats)
					- $this->_value($pf_id, $person_id, $stats)
					- $this->_value($sr_id, $person_id, $stats);
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

	function pir_season($stat_type, &$stats) {
		$p_id = $this->_stat_type_id('Points');
		$r_id = $this->_stat_type_id('Rebounds');
		$a_id = $this->_stat_type_id('Assists');
		$s_id = $this->_stat_type_id('Steals');
		$b_id = $this->_stat_type_id('Blocks');
		$fd_id = $this->_stat_type_id('Fouls Drawn');
		$fgm_id = $this->_stat_type_id('Field Goals Made');
		$fga_id = $this->_stat_type_id('Field Goals Attempted');
		$ftm_id = $this->_stat_type_id('Free Throws Made');
		$fta_id = $this->_stat_type_id('Free Throws Attempted');
		$tpfgm_id = $this->_stat_type_id('Three-point Field Goals Made');
		$tpfga_id = $this->_stat_type_id('Three-point Field Goals Attempted');
		$t_id = $this->_stat_type_id('Turnovers');
		$pf_id = $this->_stat_type_id('Personal Fouls');
		$sr_id = $this->_stat_type_id('Shots Rejected');

		$this->_init_rosters($stats);
		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->_value_sum($p_id, $person_id, $stats)
					+ $this->_value_sum($r_id, $person_id, $stats)
					+ $this->_value_sum($a_id, $person_id, $stats)
					+ $this->_value_sum($s_id, $person_id, $stats)
					+ $this->_value_sum($b_id, $person_id, $stats)
					+ $this->_value_sum($fd_id, $person_id, $stats)
					+ $this->_value_sum($fgm_id, $person_id, $stats) - $this->_value_sum($fga_id, $person_id, $stats)
					+ $this->_value_sum($ftm_id, $person_id, $stats) - $this->_value_sum($fta_id, $person_id, $stats)
					+ $this->_value_sum($tpfgm_id, $person_id, $stats) - $this->_value_sum($tpfga_id, $person_id, $stats)
					- $this->_value_sum($t_id, $person_id, $stats)
					- $this->_value_sum($pf_id, $person_id, $stats)
					- $this->_value_sum($sr_id, $person_id, $stats);
				if (Stat::applicable($stat_type, $position) || $value != 0) {
					$stats['Calculated'][$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}
}

?>
