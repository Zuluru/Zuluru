<?php

/**
 * Derived class for implementing functionality for individual signup to team events.
 */

class EventTypeIndividualComponent extends EventTypeComponent
{
	function configurationFields() {
		return array('level_of_play');
	}

	function configurationFieldsElement() {
		return 'individual';
	}
}

?>
