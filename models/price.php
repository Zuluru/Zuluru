<?php
class Price extends AppModel {
	var $name = 'Price';
	var $displayField = 'name';
	var $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Please enter a short name for the price point.',
			),
		),
		'cost' => array(
			'money' => array(
				'rule' => array('numeric'),
				'message' => 'You must enter a valid cost.',
			),
		),
		'tax1' => array(
			'money' => array(
				'rule' => array('numeric'),
				'message' => 'You must enter a valid tax amount.',
			),
		),
		'tax2' => array(
			'money' => array(
				'rule' => array('numeric'),
				'message' => 'You must enter a valid tax amount.',
			),
		),
		'open' => array(
			'date' => array(
				'rule' => array('datetime'),
				'message' => 'You must select a valid opening date.',
			),
			'range' => array(
				'rule' => array('indateconfig', 'event'),
				'message' => 'Price point open date must be between last year and next year.',
				'on' => 'create',
			),
		),
		'close' => array(
			'date' => array(
				'rule' => array('datetime'),
				'message' => 'You must select a valid closing date.',
			),
			'range' => array(
				'rule' => array('indateconfig', 'event'),
				'message' => 'Price point close date must be between last year and next year.',
				'on' => 'create',
			),
			'greater' => array(
				'rule' => array('greaterdate', 'open'),
				'message' => 'The price point close date cannot be before the open date.',
			),
		),
		'register_rule' => array(
			'valid' => array(
				'rule' => array('rule'),
				'required' => false,
				'allowEmpty' => true,
				'message' => 'There is an error in the rule syntax.',
			),
		),
		'allow_late_payment' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.enable'),
				'message' => 'You must select whether or not payment will be accepted after the close date.',
			),
		),
		'allow_deposit' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.enable'),
				'message' => 'You must select whether or not deposits will be accepted.',
			),
		),
		'fixed_deposit' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.enable'),
				'message' => 'You must select whether or not only fixed-amount deposits will be accepted.',
			),
		),
		'deposit_only' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.enable'),
				'message' => 'You must select whether deposits are all that will be accepted (e.g. if the balance will be paid off-line).',
			),
		),
		'minimum_deposit' => array(
			'money' => array(
				'rule' => array('numeric'),
				'message' => 'You must enter a valid cost.',
			),
		),
	);

	var $belongsTo = array(
		'Event' => array(
			'className' => 'Event',
			'foreignKey' => 'event_id',
		),
	);

	var $hasMany = array(
		'Registration' => array(
			'className' => 'Registration',
			'foreignKey' => 'price_id',
			'dependent' => false,
		),
	);

	function afterSave() {
		if (!empty($this->data['Price']['event_id'])) {
			$event_id = $this->data['Price']['event_id'];
		} else {
			$event_id = $this->field('event_id', array('id' => $this->id));
		}

		// Update this price's event open and close dates, if required
		$this->Event->contain();
		$event = $this->Event->read(array('open', 'close'), $event_id);

		if (empty($event['Event']['open']) || $event['Event']['open'] == '0000-00-00') {
			$event['Event']['open'] = $this->data['Price']['open'];
		} else {
			$event['Event']['open'] = min($event['Event']['open'], $this->data['Price']['open']);
		}
		$event['Event']['close'] = max($event['Event']['close'], $this->data['Price']['close']);
		$this->Event->save($event, false);
	}

	function affiliate($id) {
		return $this->Event->affiliate($this->field('event_id', array('Price.id' => $id)));
	}
}
?>
