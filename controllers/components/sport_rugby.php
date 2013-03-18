<?php
/**
 * Class for Rugby sport-specific functionality.
 */

class SportRugbyComponent extends SportComponent
{
	var $sport = 'rugby';

	function points_game($stat_type, $game, &$stats) {
		$this->_init_rosters($stats);

		$t_id = $this->_stat_type_id('Tries');
		$c_id = $this->_stat_type_id('Conversions');
		$pk_id = $this->_stat_type_id('Penalty Kicks');
		$dg_id = $this->_stat_type_id('Drop Goals');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->_value($t_id, $person_id, $stats) * 5 + $this->_value($c_id, $person_id, $stats) * 2 + $this->_value($pk_id, $person_id, $stats) * 3 + $this->_value($dg_id, $person_id, $stats) * 3;

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
