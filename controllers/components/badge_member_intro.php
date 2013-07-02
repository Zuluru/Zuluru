<?php

/**
 * Implementation of the registration callback for the "intro member" badge.
 */
class BadgeMemberIntroComponent extends Object
{
	function applicable($registration) {
		if (array_key_exists('EventType', $registration)) {
			$type = $registration['EventType'];
		} else {
			$type = $registration['Event']['EventType'];
		}
		return ($type['type'] == 'membership' && $registration['Event']['membership_type'] == 'intro' &&
				$registration['Event']['membership_begins'] <= date('Y-m-d') && $registration['Event']['membership_ends'] >= date('Y-m-d'));
	}
}

?>