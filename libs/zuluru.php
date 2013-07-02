<?php
/*
 * Library functions that don't fit anywhere else can go here.
 */

/**
 * Calculate local sunset time for a timestamp, using system-wide location.
 */
function local_sunset_for_date ($date) {
	if (!is_numeric ($date)) {
		$date = strtotime ($date);
	}

	/*
	 * value of 90 degrees 50 minutes is the angle at which
	 * the sun is below the horizon.  This is the official
	 * sunset time.  Do not use "civil twilight" zenith
	 * value of 96 degrees. It's normally about 30 minutes
	 * later in the evening than official sunset, and there
	 * is some light until then, but it's too dark for safe
	 * play.
	 */
	$zenith = 90 + (50/60);

	/* TODO: eventually, use field's actual location rather than a
	 *       system-wide location?  This would be more correct in cities
	 *       with a large east/west spread, but might be confusing to some
	 */
	$lat = (float) Configure::read('organization.latitude');
	$long = (float) Configure::read('organization.longitude');

	$end_timestamp = date_sunset ($date, SUNFUNCS_RET_TIMESTAMP, $lat, $long, $zenith, date('Z') / 3600);

	// Round down to nearest 5 minutes, and adjust for server location
	$end_timestamp = floor( $end_timestamp / 300 ) * 300 - Configure::read('timezone.adjust') * 60;
	return date('H:i:s', $end_timestamp);
}

function season($indoor) {
	// The configuration settings values have "0" for the year, to facilitate form input
	$today = date('0-m-d');
	$today_wrap = date('1-m-d');

	// Build the list of applicable seasons
	$seasons = Configure::read('options.season');
	unset($seasons['None']);
	if (empty($seasons)) {
		return 'None';
	}
	foreach (array_keys($seasons) as $season) {
		$season_indoor = Configure::read("season_is_indoor.$season");
		if ($indoor != $season_indoor) {
			unset($seasons[$season]);
		}
	}

	// Create array of which season follows which
	$seasons = array_values($seasons);
	$seasons_shift = $seasons;
	array_push($seasons_shift, array_shift($seasons_shift));
	$next = array_combine($seasons, $seasons_shift);

	// Look for the season that has started without the next one starting
	foreach ($next as $a => $b) {
		$start = Configure::read('organization.' . Inflector::slug(low($a)) . '_start');
		$end = Configure::read('organization.' . Inflector::slug(low($b)) . '_start');
		// Check for a season that wraps past the end of the year
		if ($start > $end) {
			$end[0] = '1';
		}
		if (($today >= $start && $today < $end) || ($today_wrap >= $start && $today_wrap < $end)) {
			return $a;
		}
	}
}

if (!function_exists ('stats_standard_deviation')) {

	// Function to calculate square of value - mean
	function sd_square($x, $mean) { return pow($x - $mean,2); }

	// Function to calculate standard deviation (uses sd_square)   
	function stats_standard_deviation($array) {
		// square root of sum of squares devided by N-1
		return sqrt(array_sum(array_map("sd_square", $array, array_fill(0,count($array), (array_sum($array) / count($array)) ) ) ) / (count($array)-1) );
	}

}

function array_transpose($array, $selectKey = false) {
	if (!is_array($array)) return false;
	$return = array();
	foreach($array as $key => $value) {
		if (!is_array($value)) return $array;
		if ($selectKey) {
			if (isset($value[$selectKey])) $return[] = $value[$selectKey];
		} else {
			foreach ($value as $key2 => $value2) {
				$return[$key2][$key] = $value2;
			}
		}
	}
	return $return;
}

function clean($val, $func = 'is_numeric') {
	$ret = '';
	for ($i = 0; $i < strlen ($val); ++$i)
	{
		if ($func ($val[$i]))
			$ret .= $val[$i];
	}
	return $ret;
}

function ordinal($val) {
	$n1 = $val % 100; //first remove all but the last two digits
	$n2 = ($n1 < 20 ? $val : $val % 10); //remove all but last digit unless the number is in the teens, which all should be 'th'
	$ord = ($n2==1 ? 'st' : ( ($n2==2 ? 'nd' : ($n2==3 ? 'rd' : 'th') ) ) );
	return $val . $ord;
}

function fake_id() {
	$unused_id = Configure::read ('unused_id');
	if (! $unused_id) {
		$unused_id = MIN_FAKE_ID;
	} else {
		++ $unused_id;
	}
	Configure::write ('unused_id', $unused_id);
	return $unused_id;
}

function ical_encode($text) {
	$text = strtr ($text, array(
		'\\' => '\\\\',
		',' => '\\,',
		';' => '\\;',
	));
	return $text;
}

/**
 * This class handles database transactions in a safe manner.
 * Just create an object of this type, passing the model object
 * and it will start a transaction. If not commited with the
 * commit function, it will do a rollback when destroyed.
 * This means that you can start a transaction and then just
 * return from the function if an error arises, rather than
 * making sure that the logic flow gets to the rollback point.
 */
class DatabaseTransaction {
	var $db = null;
	var $model = null;

	function __construct($model) {
		$this->model = $model;
		$this->db =& ConnectionManager::getDataSource($this->model->useDbConfig);
		$this->db->begin($this->model);
	}

	function __destruct() {
		$this->rollback();
	}

	function commit() {
		if ($this->model !== null) {
			$ret = $this->db->commit($this->model);
			$this->model = null;
			return $ret;
		}
		return false;
	}

	function rollback() {
		if ($this->model !== null) {
			$ret = $this->db->rollback($this->model);
			$this->model = null;
			return $ret;
		}
		return false;
	}
}

?>
