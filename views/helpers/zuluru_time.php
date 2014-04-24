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
			$time_format = reset(Configure::read('options.time_formats'));
		}
		return $this->format($time_format, $date);
	}

	function date($date) {
		if ($date == '0000-00-00' || $date === null) {
			return __('unknown', true);
		} else if (strpos($date, '00-00') !== false) {
			// Some dates may only have a valid year portion
			return substr($date, 0, 4);
		} else {
			$date_format = Configure::read('personal.date_format');
		}
		if (empty ($date_format)) {
			$date_format = reset(Configure::read('options.date_formats'));
		}
		return $this->format($date_format, $date);
	}

	function datetime($date) {
		$date_format = Configure::read('personal.date_format');
		if (empty ($date_format)) {
			$date_format = reset(Configure::read('options.date_formats'));
		}
		$time_format = Configure::read('personal.time_format');
		if (empty ($time_format)) {
			$time_format = reset(Configure::read('options.time_formats'));
		}
		return $this->format("$date_format $time_format", $date);
	}

	function day($date) {
		$day_format = Configure::read('personal.day_format');
		if (empty ($day_format)) {
			$day_format = reset(Configure::read('options.day_formats'));
		}
		return $this->format($day_format, $date);
	}

	function fulldate($date) {
		$day_format = Configure::read('personal.day_format');
		if (empty ($day_format)) {
			$day_format = reset(Configure::read('options.day_formats'));
		}
		return $this->format("$day_format, Y", $date);
	}

	function fulldatetime($date) {
		$day_format = Configure::read('personal.day_format');
		if (empty ($day_format)) {
			$day_format = reset(Configure::read('options.day_formats'));
		}
		$time_format = Configure::read('personal.time_format');
		if (empty ($time_format)) {
			$time_format = reset(Configure::read('options.time_formats'));
		}
		return $this->format("$day_format, Y $time_format", $date);
	}

	function displayRange($start, $end) {
		// Figure out how best to display the date(s)
		$start_time = strtotime($start);
		$end_time = strtotime($end);
		$single_date = (date('z', $start_time) == date('z', $end_time));
		$single_month = (date('n', $start_time) == date('n', $end_time));
		$single_year = (date('Y', $start_time) == date('Y', $end_time));
		$entire_month = (date('j', $start_time) == 1 && date('j', $end_time) == date('t', $end_time));

		$ret = '';
		if ($single_date) {
			$ret .= date('F j, Y', strtotime($start));
		} else if ($single_month) {
			if ($entire_month) {
				$ret .= date('F Y', $start_time);
			} else {
				$ret .= date('F j', $start_time) . '-' . date('j, Y', $end_time);
			}
		} else if ($entire_month) {
			if ($single_year) {
				$ret .= date('F', $start_time);
			} else {
				$ret .= date('F Y', $start_time);
			}
			$ret .= __(' to ', true) . date('F, Y', $end_time);
		} else {
			if ($single_year) {
				$ret .= date('F j', $start_time);
			} else {
				$ret .= date('F j, Y', $start_time);
			}
			$ret .= __(' to ', true) . date('F j, Y', $end_time);
		}

		return $ret;
	}

	function format($format, $date) {
		if (empty($date)) {
			return null;
		}
		return parent::format($format, $date);
	}
}

?>
