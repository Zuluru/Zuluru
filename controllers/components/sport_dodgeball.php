<?php
/**
 * Class for Dodgeball sport-specific functionality.
 */

class SportDodgeballComponent extends SportComponent
{
	var $sport = 'dodgeball';

	function points_game($stat_type, $game, &$stats) {
		$this->_init_rosters($stats);

		$kp_id = $this->_stat_type_id('Kills');
		$km_id = $this->_stat_type_id('Killed');
		$cp_id = $this->_stat_type_id('Catches');
		$cm_id = $this->_stat_type_id('Caught');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->_value($kp_id, $person_id, $stats) - $this->_value($km_id, $person_id, $stats)
						+ ($this->_value($cp_id, $person_id, $stats) - $this->_value($cm_id, $person_id, $stats)) * 2;

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
}

?>