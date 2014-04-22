<?php

/**
 * Derived class for implementing functionality for spirit scoring without any questionnaire.
 */

class SpiritNoneComponent extends SpiritComponent
{
	var $render_element = 'none';

	var $description = 'This selection will result in no spirit questions being asked.';

	function maxs() {
		return Configure::read('scoring.spirit_max');
	}
}

?>
