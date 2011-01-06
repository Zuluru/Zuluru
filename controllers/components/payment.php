<?php
/**
 * Base class for payment provider functionality.
 */

class PaymentComponent extends Object
{
	function process($data) {
		return 'Payment processor does not have a "process" function defined!';
	}
}

?>
