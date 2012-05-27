<?php

/**
 * Derived class for implementing functionality for spirit scoring without any questionnaire.
 */

class SpiritNoneComponent extends SpiritComponent
{
	var $render_element = 'none';

	function maxs() {
		return Configure::read('scoring.spirit_max');
	}
}

?>
