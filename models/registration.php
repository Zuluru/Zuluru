<?php
class Registration extends AppModel {
	var $name = 'Registration';
	var $displayField = 'id';
	var $actsAs = array('WhoDidIt' => array(
		'created_by_field' => 'person_id',
		'auto_bind' => false,
	));

	var $validate = array(
//		'payment' => array(
//			'inlist' => array(
//				'rule' => array('inlist'),
//				'message' => 'TODO',
//			),
//		),
	);

	var $belongsTo = array(
		'Person' => array(
			'className' => 'Person',
			'foreignKey' => 'person_id',
		),
		'Event' => array(
			'className' => 'Event',
			'foreignKey' => 'event_id',
		),
		'Price' => array(
			'className' => 'Price',
			'foreignKey' => 'price_id',
		),
	);

	var $hasMany = array(
		'Payment' => array(
			'className' => 'Payment',
			'foreignKey' => 'registration_id',
			'dependent' => true,
		),
		'Response' => array(
			'className' => 'Response',
			'foreignKey' => 'registration_id',
			'dependent' => true,
		),
	);

	function affiliate($id) {
		return $this->Event->affiliate($this->field('event_id', array('Registration.id' => $id)));
	}

	static function paymentAmounts($registration) {
		$current_total = $registration['Price']['cost'] + $registration['Price']['tax1'] + $registration['Price']['tax2'];
		if ($current_total == 0) {
			return array(0,0,0);
		}

		$tax1_percent = $registration['Price']['tax1'] / $current_total;
		$tax2_percent = $registration['Price']['tax2'] / $current_total;

		$total = $registration['Registration']['total_amount'];

		if ($registration['Registration']['deposit_amount'] > 0 && in_array($registration['Registration']['payment'], Configure::read('registration_none_paid'))) {
			// Payment amount is the deposit to be paid
			$payment = $registration['Registration']['deposit_amount'];
		} else {
			// Break apart the outstanding amount
			$payment = $total - array_sum(Set::extract('/Payment/payment_amount', $registration));
		}

		$tax1 = round($payment * $tax1_percent, 2);
		$tax2 = round($payment * $tax2_percent, 2);
		$cost = $payment - $tax1 - $tax2;

		return array($cost, $tax1, $tax2);
	}

	function longDescription($data) {
		$name = $data['Event']['name'];
		$extras = array();
		if (!empty($data['Price']['name'])) {
			$extras[] = $data['Price']['name'];
		}

		if ($data['Registration']['deposit_amount'] > 0) {
			$paid = array_sum(Set::extract('/Payment/payment_amount', $data));
			if (in_array($data['Registration']['payment'], Configure::read('registration_none_paid'))) {
				$extras[] = __('Deposit', true);
			} else {
				$extras[] = __('Remaining balance', true);
			}
		}

		if (!empty($extras)) {
			$name .= ' (' . implode(', ', $extras) . ')';
		}
		return $name;
	}

}
?>