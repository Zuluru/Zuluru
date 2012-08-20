<?php

if (function_exists ('mhash'))
{
	// Use mhash function to compute the hash.
	function hmac($key, $data) {
		return (bin2hex (mhash(MHASH_MD5, $data, $key))); 
	}

} else {

	function hmac($key, $data) {
		// RFC 2104 HMAC implementation for php to compute the MD5 HMAC.
		// Credit: Lance Rushing - http://www.php.net/manual/en/function.mhash.php

		$b = 64; // byte length for MD5
		if (strlen($key) > $b) {
			$key = pack("H*",md5($key));
		}
		$key  = str_pad($key, $b, chr(0x00));
		$ipad = str_pad('', $b, chr(0x36));
		$opad = str_pad('', $b, chr(0x5c));
		$k_ipad = $key ^ $ipad ;
		$k_opad = $key ^ $opad;

		return md5($k_opad  . pack("H*",md5($k_ipad . $data)));
	}

}

// JavaScript for no address bar
$this->Html->scriptBlock ('
function open_payment_window()
{
	window.open("", "payment_window", "menubar=1,toolbar=1,scrollbars=1,resizable=1,status=1,location=0");
	var a = window.setTimeout("document.payment_form.submit();", 500);
}
', array('inline' => false));

$order_fmt = Configure::read('registration.order_id_format');

// Generate a unique order id
$time = time();
$invoice_num = sprintf($order_fmt, $registrations[0]['Registration']['id']);
$unique_order_num = $invoice_num . sprintf('-%010d', $time);

// Build the online payment form
$login = Configure::read('payment.chase_live_store');
$key = Configure::read('payment.chase_live_password');

$form_options = array('url' => 'https://checkout.e-xact.com/payment', 'name' => 'payment_form');
$submit_options = array('div' => false);

if (Configure::read('payment.popup')) {
	$form_options['target'] = 'payment_window';
	$submit_options['onClick'] = 'open_payment_window();';
}

echo $this->Form->create(false, $form_options);

function quick_hidden (&$ths, $name, $value) {
	return $ths->Form->hidden($name, array('name' => $name, 'value' => $value));
}

echo quick_hidden($this, 'x_login', $login);
echo quick_hidden($this, 'x_test_request', $payment_obj->isTest() ? 'TRUE' : 'FALSE');
echo quick_hidden($this, 'x_fp_sequence', $unique_order_num);
echo quick_hidden($this, 'x_fp_timestamp', $time);
echo quick_hidden($this, 'x_show_form', 'PAYMENT_FORM');
echo quick_hidden($this, 'x_type', 'AUTH_CAPTURE');
echo quick_hidden($this, 'x_receipt_link_method', 'GET');
echo quick_hidden($this, 'x_relay_response', 'TRUE');

$join = '<|>';
$currency = Configure::read('payment.currency');
$amount = $tax = 0;
$ids = array();
foreach ($registrations as $registration) {
	echo quick_hidden($this, 'x_line_item', implode ($join, array(
			sprintf(Configure::read('payment.reg_id_format'), $registration['Event']['id']),
			$registration['Event']['name'],
			$registration['Event']['payment_desc'],
			'1',
			$registration['Event']['cost'],
			($registration['Event']['tax1'] + $registration['Event']['tax2'] > 0) ? 'YES' : 'NO',
	)) . $join);
	$amount += $registration['Event']['cost'] + $registration['Event']['tax1'] + $registration['Event']['tax2'];
	$tax += $registration['Event']['tax1'] + $registration['Event']['tax2'];
	$ids[] = $registration['Registration']['id'];
}
$amount = sprintf('%.2f', $amount);
$hash_source = implode ('^', array (
		$login,
		$unique_order_num,
		$time,
		$amount,
		$currency,
));
echo quick_hidden($this, 'x_fp_hash', hmac($key, $hash_source));
echo quick_hidden($this, 'x_description', implode (',', $ids));

echo quick_hidden($this, 'x_cust_id', $person['Person']['id']);
echo quick_hidden($this, 'x_email', $person['Person']['email']);
echo quick_hidden($this, 'x_invoice_num', $invoice_num);
echo quick_hidden($this, 'x_currency_code', $currency);
echo quick_hidden($this, 'x_amount', $amount);
if ($tax > 0) {
	echo quick_hidden($this, 'x_tax', $tax);
}
echo quick_hidden($this, 'x_first_name', $person['Person']['first_name']);
echo quick_hidden($this, 'x_last_name', $person['Person']['last_name']);
echo quick_hidden($this, 'x_address', $person['Person']['addr_street']);
echo quick_hidden($this, 'x_city', $person['Person']['addr_city']);
echo quick_hidden($this, 'x_state', $person['Person']['addr_prov']);
echo quick_hidden($this, 'x_zip', $person['Person']['addr_postalcode']);
echo quick_hidden($this, 'x_country', $person['Person']['addr_country']);
echo quick_hidden($this, 'x_phone', $person['Person']['home_phone']);

echo $this->Form->submit('Pay', $submit_options);

echo $this->Form->end();

?>
