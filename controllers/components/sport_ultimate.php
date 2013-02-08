<?php
/**
 * Class for Ultimate sport-specific functionality.
 */

class SportUltimateComponent extends SportComponent
{
	var $sport = 'ultimate';

	function points_game($stat_id, $game, &$stats) {
		$this->_game_sum($stat_id, $game, $stats, array('Goals', 'Assists', 'Second Assists'));
	}

	function turnovers_game($stat_id, $game, &$stats) {
		$this->_game_sum($stat_id, $game, $stats, array('Throwaways', 'Drops'));
	}
}

?>
