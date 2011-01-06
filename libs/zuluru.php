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

	// Round down to nearest 5 minutes
	$end_timestamp = floor( $end_timestamp / 300 ) * 300;
	return date('H:i:s', $end_timestamp);
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

function clean($val, $func = 'is_numeric')
{
	$ret = '';
	for ($i = 0; $i < strlen ($val); ++$i)
	{
		if ($func ($val[$i]))
			$ret .= $val[$i];
	}
	return $ret;
}

?>
