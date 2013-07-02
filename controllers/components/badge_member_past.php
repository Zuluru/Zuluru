<?php

/**
 * Implementation of the registration callback for the "past member" badge.
 */
class BadgeMemberPastComponent extends Object
{
	function applicable($registration) {
		if (array_key_exists('EventType', $registration)) {
			$type = $registration['EventType'];
		} else {
			$type = $registration['Event']['EventType'];
		}
		return ($type['type'] == 'membership' && $registration['Event']['membership_ends'] < date('Y-m-d'));
	}
}

?>