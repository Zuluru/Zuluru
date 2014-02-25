<?php
class Waiver extends AppModel {
	var $name = 'Waiver';
	var $displayField = 'name';
	var $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Name cannot be blank',
				'required' => true,
			),
		),
		'affiliate_id' => array(
			'inlist' => array(
				'rule' => array('inquery', 'Affiliate', 'id'),
				'message' => 'You must select a valid affiliate.',
			),
		),
		'text' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Waiver text cannot be blank',
			),
		),
		'active' => array(
			'boolean' => array(
				'rule' => array('boolean'),
			),
		),
		'expiry_type' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.waivers.expiry_type'),
				'message' => 'You must select a valid expiry type.',
			),
		),
		'duration' => array(
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
	);

	var $belongsTo = array(
		'Affiliate' => array(
			'className' => 'Affiliate',
			'foreignKey' => 'affiliate_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);

	var $hasAndBelongsToMany = array(
		'Person' => array(
			'className' => 'Person',
			'joinTable' => 'waivers_people',
			'foreignKey' => 'waiver_id',
			'associationForeignKey' => 'person_id',
			'unique' => true,
		),
	);

	function canSign($date) {
		// You can't sign a waiver in the past
		list ($start, $end) = $this->validRange($date);
		if ($date < $start) {
			return false;
		}

		// You can't sign a waiver more than a year in the future
		if (strtotime($date) > time() + YEAR) {
			return false;
		}

		return true;
	}

	function signed($date, $waivers) {
		$signed = Set::extract ("/WaiversPerson[waiver_id={$this->data['Waiver']['id']}][valid_from<=$date][valid_until>=$date]", $waivers);
		return !empty($signed);
	}

	function validFrom($date) {
		switch ($this->data['Waiver']['expiry_type']) {
			case 'fixed_dates':
				$range = $this->validRange($date);
				return $range[0];

			case 'elapsed_time':
				return date('Y-m-d');

			case 'event':
				return $date;
		}
	}

	function validUntil($date) {
		switch ($this->data['Waiver']['expiry_type']) {
			case 'fixed_dates':
				$range = $this->validRange($date);
				return $range[1];

			case 'elapsed_time':
				return date('Y-m-d', strtotime("+{$this->data['Waiver']['duration']} days"));

			case 'event':
				return $date;
		}
	}

	function validRange($date, $waiver = null) {
		if (!$waiver) {
			$waiver = $this->data['Waiver'];
		}
		return $this->_validRange($date, $waiver);
	}

	function _validRange($date, $waiver) {
		switch ($waiver['expiry_type']) {
			case 'fixed_dates':
				$target = strtotime($date);
				$y = date('Y', $target);
				while (true) {
					$end = mktime(23, 59, 59, $waiver['end_month'], $waiver['end_day'], $y);
					if ($end > $target) {
						break;
					}
					++ $y;
				}
				$start = mktime(0, 0, 0, $waiver['start_month'], $waiver['start_day'], $y);
				if ($end < $start) {
					-- $y;
					$start = mktime(0, 0, 0, $waiver['start_month'], $waiver['start_day'], $y);
				}
				if ($start <= $target && $target <= $end) {
					return array(date('Y-m-d', $start), date('Y-m-d', $end));
				}
				return array(false, false);

			case 'elapsed_time':
				return array(date('Y-m-d'), date('Y-m-d', strtotime("+{$waiver['duration']} days")));

			case 'event':
				return array($date, $date);
		}
	}

	function affiliate($id) {
		return $this->field('affiliate_id', array('Waiver.id' => $id));
	}
}
?>