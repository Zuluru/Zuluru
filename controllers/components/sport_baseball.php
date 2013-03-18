<?php
/**
 * Class for Baseball sport-specific functionality.
 */

class SportBaseballComponent extends SportComponent
{
	var $sport = 'baseball';

	function hits_game($stat_type, $game, &$stats) {
		$this->_game_sum($stat_type, $game, $stats, array('Singles', 'Doubles', 'Triples', 'Home Runs'));
	}

	function innings_season($stat_type, &$stats) {
		$ip_id = $this->_stat_type_id('Innings Pitched');
		$positions = Set::extract("/StatType[id={$stat_type['id']}]/positions", $this->stat_types);
		$positions = explode(',', $positions[0]);

		$this->_init_rosters($stats);
		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				$innings = Set::extract("/Stat[stat_type_id=$ip_id][person_id=$person_id]/value", $stats);
				if (empty($innings)) {
					$value = 'N/A';
				} else {
					$value = $this->innings($innings);
				}
				if (Stat::applicable($stat_type, $position) || $value != 'N/A') {
					$stats['Calculated'][$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

	function era_season($stat_type, &$stats) {
		$er_id = $this->_stat_type_id('Earned Runs');
		$ip_id = $this->_stat_type_id('Innings Pitched');
		$positions = Set::extract("/StatType[id={$stat_type['id']}]/positions", $this->stat_types);
		$positions = explode(',', $positions[0]);

		$this->_init_rosters($stats);
		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				$innings = Set::extract("/Stat[stat_type_id=$ip_id][person_id=$person_id]/value", $stats);
				if (empty($innings)) {
					$value = 'N/A';
				} else {
					$outs = $this->outs($innings);
					$ip = $outs / 3;
					$value = sprintf('%.02f', $this->_value_sum($er_id, $person_id, $stats) * 9 / $ip);
				}
				if (Stat::applicable($stat_type, $position) || $value != 'N/A') {
					$stats['Calculated'][$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

	// Handle the baseball standard of "6.2" meaning "six full innings plus two outs"
	function outs($innings) {
		$outs = 0;
		foreach ($innings as $i) {
			if (strpos($i, '.') !== false) {
				list($i,$o) = explode('.', $i);
			} else {
				$o = 0;
			}
			$outs += $i * 3 + $o;
		}
		return $outs;
	}

	function innings_sum($innings) {
		$outs = $this->outs($innings);
		$innings = floor($outs / 3);
		$outs %= 3;
		if ($outs > 0) {
			$innings .= ".$outs";
		}
		return $innings;
	}

	function ba_season($stat_type, &$stats) {
		$h_id = $this->_stat_type_id('Hits');
		$ab_id = $this->_stat_type_id('At Bats');

		$this->_init_rosters($stats);
		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				$value = sprintf('%.03f', $this->_value_sum($h_id, $person_id, $stats) / $this->_value_sum($ab_id, $person_id, $stats));
				if (Stat::applicable($stat_type, $position) || $value != 0) {
					$stats['Calculated'][$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

	function obp_season($stat_type, &$stats) {
		$h_id = $this->_stat_type_id('Hits');
		$bb_id = $this->_stat_type_id('Walks');
		$hbp_id = $this->_stat_type_id('Hit By Pitch');
		$sf_id = $this->_stat_type_id('Sacrifice Flies');
		$ab_id = $this->_stat_type_id('At Bats');

		$this->_init_rosters($stats);
		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				$reached = $this->_value_sum($h_id, $person_id, $stats) + $this->_value_sum($bb_id, $person_id, $stats) + $this->_value_sum($hbp_id, $person_id, $stats);
				$appearances = $this->_value_sum($ab_id, $person_id, $stats) + $this->_value_sum($bb_id, $person_id, $stats) + $this->_value_sum($sf_id, $person_id, $stats) + $this->_value_sum($hbp_id, $person_id, $stats);
				$value = sprintf('%.03f', $reached / $appearances);
				if (Stat::applicable($stat_type, $position) || $value != 0) {
					$stats['Calculated'][$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

	function slg_season($stat_type, &$stats) {
		$b1_id = $this->_stat_type_id('Singles');
		$b2_id = $this->_stat_type_id('Doubles');
		$b3_id = $this->_stat_type_id('Triples');
		$b4_id = $this->_stat_type_id('Home Runs');
		$ab_id = $this->_stat_type_id('At Bats');

		$this->_init_rosters($stats);
		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				$bases = $this->_value_sum($b1_id, $person_id, $stats) +
						($this->_value_sum($b2_id, $person_id, $stats) * 2) +
						($this->_value_sum($b3_id, $person_id, $stats) * 3) +
						($this->_value_sum($b4_id, $person_id, $stats) * 4);
				$value = sprintf('%.03f', $bases / $this->_value_sum($ab_id, $person_id, $stats));
				if (Stat::applicable($stat_type, $position) || $value != 0) {
					$stats['Calculated'][$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

	function ops_season($stat_type, &$stats) {
		$h_id = $this->_stat_type_id('Hits');
		$b1_id = $this->_stat_type_id('Singles');
		$b2_id = $this->_stat_type_id('Doubles');
		$b3_id = $this->_stat_type_id('Triples');
		$b4_id = $this->_stat_type_id('Home Runs');
		$bb_id = $this->_stat_type_id('Walks');
		$hbp_id = $this->_stat_type_id('Hit By Pitch');
		$sf_id = $this->_stat_type_id('Sacrifice Flies');
		$ab_id = $this->_stat_type_id('At Bats');

		$this->_init_rosters($stats);
		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				$bases = $this->_value_sum($b1_id, $person_id, $stats) +
						($this->_value_sum($b2_id, $person_id, $stats) * 2) +
						($this->_value_sum($b3_id, $person_id, $stats) * 3) +
						($this->_value_sum($b4_id, $person_id, $stats) * 4);
				$reached = $this->_value_sum($h_id, $person_id, $stats) + $this->_value_sum($bb_id, $person_id, $stats) + $this->_value_sum($hbp_id, $person_id, $stats);
				$appearances = $this->_value_sum($ab_id, $person_id, $stats) + $this->_value_sum($bb_id, $person_id, $stats) + $this->_value_sum($sf_id, $person_id, $stats) + $this->_value_sum($hbp_id, $person_id, $stats);
				$value = sprintf('%.03f', $reached / $appearances + $bases / $this->_value_sum($ab_id, $person_id, $stats));
				if (Stat::applicable($stat_type, $position) || $value != 0) {
					$stats['Calculated'][$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}
}

?>
