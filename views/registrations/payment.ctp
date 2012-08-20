<?php
$this->Html->addCrumb (__('Online Transaction Result', true));
?>

<?php
if ($result === true) {
	echo $this->element('payments/invoices/' . Configure::read('payment.invoice_implementation'));
	foreach ($errors as $error) {
		echo $this->Html->para('error-message', $error);
	}
} else {
	echo $this->Html->para('error-message', 'Your payment was declined. The reason given was:');
	echo $this->Html->para('error-message', $audit['message']);
	echo $this->element('payments/offline');
	echo $this->Html->para(null, 'Alternately, you can ' .
		$this->Html->link('return to the checkout page', "http://{$_SERVER["SERVER_NAME"]}/", array('onclick' => 'close_and_redirect("' . $this->Html->url(array('controller' => 'registrations', 'action' => 'checkout'), true) . '")')) .
		' and try a different payment option.');
}

if (Configure::read('payment.popup')) {
	echo $this->Html->para(null, 'Click ' .
		$this->Html->link('here', "http://{$_SERVER["SERVER_NAME"]}/", array('onclick' => 'close_and_redirect("' . $this->Html->url(array('controller' => 'events', 'action' => 'wizard'), true) . '")')) .
		' to close this window.');
	$this->Html->scriptBlock('
function close_and_redirect(url)
{
	window.opener.location.href = url;
	window.close();
}
	', array('inline' => false));
}

?>
