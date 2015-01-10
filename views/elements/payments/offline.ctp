<?php
$offline = Configure::read('registration.offline_payment_text');
if (!empty($offline)) {
	if (Configure::read('registration.online_payments')) {
		$options = Configure::read('payment.offline_options');
		if (!empty($options)) {
			echo $this->Html->para(null, sprintf(__('If you prefer to pay offline (via %s), the online portion of your registration process is now complete, but you must do the following to make payment:', true), $options));
		}
	} else {
		echo $this->Html->para(null, __('The online portion of your registration process is now complete, but you must do the following to make payment:', true));
	}
	echo $offline;
}
?>
