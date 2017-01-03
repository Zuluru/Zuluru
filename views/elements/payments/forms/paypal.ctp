<?php
// JavaScript for no address bar
$this->Html->scriptBlock ('
function open_payment_window()
{
	window.open("", "payment_window", "menubar=1,toolbar=1,scrollbars=1,resizable=1,status=1,location=0");
	var a = window.setTimeout("document.payment_form.submit();", 500);
}
', array('inline' => false));

$invnum = sprintf(Configure::read('registration.order_id_format'), $registrations[0]['Registration']['id']);
if (!empty($registrations[0]['Payment'])) {
	$invnum .= '-' . (count($registrations[0]['Payment']) + 1);
}

if (!empty($person['Person']['email'])) {
	$email = $person['Person']['email'];
} else if (!empty($person['Person']['alternate_email'])) {
	$email = $person['Person']['alternate_email'];
} else {
	foreach ($person['Related'] as $related) {
		if (!empty($related['email'])) {
			$email = $related['email'];
			break;
		} else if (!empty($related['alternate_email'])) {
			$email = $related['alternate_email'];
			break;
		}
	}
}

$fields = array(
		'RETURNURL' => $this->Html->url(array('controller' => 'registrations', 'action' => 'payment'), true),
		'CANCELURL' => $this->Html->url(array('controller' => 'registrations', 'action' => 'checkout'), true),
		'SOLUTIONTYPE' => 'Sole',
		'REQCONFIRMSHIPPING' => 0,
		'NOSHIPPING' => 1,
		'EMAIL' => $email,
		'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
		'PAYMENTREQUEST_0_SHIPTONAME' => "{$person['Person']['first_name']} {$person['Person']['last_name']}",
		'PAYMENTREQUEST_0_SHIPTOSTREET' => $person['Person']['addr_street'],
		'PAYMENTREQUEST_0_SHIPTOCITY' => $person['Person']['addr_city'],
		'PAYMENTREQUEST_0_SHIPTOSTATE' => $person['Person']['addr_prov'],
		'PAYMENTREQUEST_0_SHIPTOZIP' => $person['Person']['addr_postalcode'],
		'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE' => $person['Person']['addr_country'],
		'PAYMENTREQUEST_0_SHIPTOPHONENUM' => $person['Person']['home_phone'],
		'PAYMENTREQUEST_0_INVNUM' => $invnum,
		'PAYMENTREQUEST_0_CURRENCYCODE' => Configure::read('payment.currency'),
);

$total_amount = $total_tax = 0;
$ids = array();
$m = 0;
foreach ($registrations as $registration) {
	list ($cost, $tax1, $tax2) = Registration::paymentAmounts($registration);

	$fields["L_PAYMENTREQUEST_0_NAME$m"] = $registration['Event']['name'];
	$fields["L_PAYMENTREQUEST_0_DESC$m"] = $registration['Event']['payment_desc'];
	$fields["L_PAYMENTREQUEST_0_AMT$m"] = sprintf ('%.2f', $cost);
	$fields["L_PAYMENTREQUEST_0_TAXAMT$m"] = sprintf ('%.2f', $tax1 + $tax2);
	$fields["L_PAYMENTREQUEST_0_NUMBER$m"] = sprintf(Configure::read('payment.reg_id_format'), $registration['Event']['id']);
	$fields["L_PAYMENTREQUEST_0_QTY$m"] = 1;

	$total_amount += $cost + $tax1 + $tax2;
	$total_tax += $tax1 + $tax2;
	$ids[] = $registration['Registration']['id'];
	++ $m;
}
$fields['PAYMENTREQUEST_0_CUSTOM'] = $person['Person']['id'] . ':' . implode (',', $ids);
$fields['PAYMENTREQUEST_0_AMT'] = sprintf('%.2f', $total_amount);
$fields['PAYMENTREQUEST_0_ITEMAMT'] = sprintf('%.2f', $total_amount - $total_tax);
if ($total_tax > 0) {
	$fields['PAYMENTREQUEST_0_TAXAMT'] = $total_tax;
}

$response = $payment_obj->fetch('SetExpressCheckout', $fields);
if (is_array($response)) {
	// Build the online payment form
	if ($payment_obj->isTest()) {
		$paypal_url = 'https://www.sandbox.paypal.com/';
	} else {
		$paypal_url = 'https://www.paypal.com/';
	}

	$url = "{$paypal_url}webscr?cmd=_express-checkout&token=" . urlencode($response['TOKEN']);

	$form_options = array('url' => $url, 'name' => 'payment_form', 'escape' => false);
	$submit_options = array('div' => false, 'alt' => 'Pay');
	if (Configure::read('payment.popup')) {
		$form_options['target'] = 'payment_window';
		$submit_options['onClick'] = 'open_payment_window();';
	}

	echo $this->Form->create(false, $form_options);
	echo $this->Form->submit('https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif', $submit_options);
	echo $this->Form->end();
} else {
	echo $this->Js->buffer("alert('$response');");
}

?>
