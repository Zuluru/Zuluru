<?php
/**
 * Base class for payment provider functionality.
 */

class PaymentComponent extends Object
{
	function process($data) {
		return 'Payment processor does not have a "process" function defined!';
	}

	function isTest() {
		$test_config = Configure::read('payment.test_payments');
		switch ($test_config)
		{
			case 1:
				return true;

			case 2:
				return $this->_controller->is_admin;

			default:
				return false;
		}
	}
}

?>
