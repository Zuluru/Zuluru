<?php
/**
 * Class for handling payments from the Chase hosted checkout system.
 */

class PaymentChaseComponent extends PaymentComponent
{
	function process($data) {
		// Chase posts data back to us as if we're a form
		$data = $data['form'];

		// Retrieve the parameters sent from the server
		$audit = array(
			'order_id' => $data['Reference_No'],
			'response_code' => $data['Bank_Resp_Code'],
			'iso_code' => $data['x_response_code'],
			'transaction_id' => $data['x_trans_id'],
			'approval_code' => $data['x_auth_code'],
			'charge_total' => $data['x_amount'],
			'cardholder' => $data['CardHoldersName'],
			'expiry' => $data['Expiry_Date'],
			'f4l4' => $data['Card_Number'],
			'card' => $data['TransactionCardType'],
			'message' => $data['Bank_Message'],
		);

		// See if we can get a better card number from the receipt
		if (preg_match ('#CARD NUMBER : (\*+\d+)#im', $data['exact_ctr'], $matches))
		{
			$audit['f4l4'] = $matches[1];
		}

		// TODO: no better way to get these from the response?
		if (stripos ($audit['card'], 'interac') === false) {
			$audit['transaction_name'] = 'purchase';
			$audit['issuer'] = $audit['issuer_invoice'] = $audit['issuer_confirmation'] = '';
		} else {
			$audit['transaction_name'] = 'idebit_purchase';
			$audit['issuer'] = $data['exact_issname'];
			$audit['issuer_invoice'] = $data['x_invoice_num'];
			$audit['issuer_confirmation'] = $data['exact_issconf'];
		}
		preg_match ('#DATE/TIME   : (\d+ [a-z]{3} \d+) (\d+:\d+:\d+)#im', $data['exact_ctr'], $matches);
		$audit['date'] = $matches[1];
		$audit['time'] = $matches[2];

		// Validate the hash
		$login = Configure::read('payment.chase_live_store');
		$key = Configure::read('payment.chase_live_password');
		$calculated_hash = md5("$key$login{$audit['transaction_id']}{$audit['charge_total']}");

		// Validate the response code
		if ($audit['iso_code'] == 1 && $data['x_MD5_Hash'] == $calculated_hash)
		{
			$registration_ids = explode (',', $data['x_description']);
			return array(true, $audit, $registration_ids);
		}

		else {
			return array(false, $audit, array());
		}
	}
}

?>
