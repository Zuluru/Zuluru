<?php

/**
 * Extend the default time helper by providing some default formats,
 * which can be overridden on a per-user basis.
 * 
 * TODO: Maybe make use of $this->nice, or at least copy features from it?
 * TODO: Use the 4th parameter to $this->format for the user's timezone offset
 */
class ZuluruTimeHelper extends TimeHelper {
	function time($date) {
		$time_format = Configure::read('personal.time_format');
		if (empty ($time_format)) {
			$time_format = array_shift (Configure::read('options.time_formats'));
		}
		return $this->format($time_format, $date);
	}

	function date($date) {
		$date_format = Configure::read('personal.date_format');
		if (empty ($date_format)) {
			$date_format = array_shift (Configure::read('options.date_formats'));
		}
		return $this->format($date_format, $date);
	}

	function datetime($date) {
		$date_format = Configure::read('personal.date_format');
		if (empty ($date_format)) {
			$date_format = array_shift (Configure::read('options.date_formats'));
		}
		$time_format = Configure::read('personal.time_format');
		if (empty ($time_format)) {
			$time_format = array_shift (Configure::read('options.time_formats'));
		}
		return $this->format("$date_format $time_format", $date);
	}

	function day($date) {
		$day_format = Configure::read('personal.day_format');
		if (empty ($day_format)) {
			$day_format = array_shift (Configure::read('options.day_formats'));
		}
		return $this->format($day_format, $date);
	}

	function fulldate($date) {
		$day_format = Configure::read('personal.day_format');
		if (empty ($day_format)) {
			$day_format = array_shift (Configure::read('options.day_formats'));
		}
		return $this->format("$day_format, Y", $date);
	}

	function fulldatetime($date) {
		$day_format = Configure::read('personal.day_format');
		if (empty ($day_format)) {
			$day_format = array_shift (Configure::read('options.day_formats'));
		}
		$time_format = Configure::read('personal.time_format');
		if (empty ($time_format)) {
			$time_format = array_shift (Configure::read('options.time_formats'));
		}
		return $this->format("$day_format, Y $time_format", $date);
	}

	function format($format, $date) {
		if (empty($date)) {
			return null;
		}
		return parent::format($format, $date);
	}
}

?>
