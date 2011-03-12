<?php
class Event extends AppModel {
	var $name = 'Event';
	var $displayField = 'name';
	var $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'A valid event name must be entered.',
			),
		),
		'description' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Please enter a description of the event.',
			),
		),
		'event_type_id' => array(
			'inlist' => array(
				'rule' => array('inquery', 'EventType', 'id'),
				'message' => 'You must select a valid event type.',
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
				'message' => 'Event open date must be between last year and next year.',
			),
		),
		'close' => array(
			'date' => array(
				'rule' => array('datetime'),
				'message' => 'You must select a valid closing date.',
			),
			'range' => array(
				'rule' => array('indateconfig', 'event'),
				'message' => 'Event close date must be between last year and next year.',
			),
			'greater' => array(
				'rule' => array('greaterdate', 'open'),
				'message' => 'The event close date must be after the open date.',
			),
		),
		'cap_male' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'You must enter a number for the male cap.',
			),
		),
		'cap_female' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'You must enter a number for the female cap.',
			),
		),
		'multiple' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				'required' => false,
				'allowEmpty' => true,
				'message' => 'Indicate whether multiple registrations are allowed.',
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
	);

	var $belongsTo = array(
		'EventType' => array(
			'className' => 'EventType',
			'foreignKey' => 'event_type_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Questionnaire' => array(
			'className' => 'Questionnaire',
			'foreignKey' => 'questionnaire_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	var $hasMany = array(
		'Preregistration' => array(
			'className' => 'Preregistration',
			'foreignKey' => 'event_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Registration' => array(
			'className' => 'Registration',
			'foreignKey' => 'event_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);

	// Determine the applicable cap
	// TODO: Any way to put this in _afterFind? How would we find the user's gender? Session/Auth info?
	static function cap ($male, $female, $gender) {
		if ($female == -2)
		{
			return $male;
		}
		return ($gender == 'Male' ? $male : $female);
	}

	// TODO: Add validation details before rendering, so required fields are properly highlighted
	function beforeValidate() {
		$event_obj = AppController::_getComponent ('EventType', $this->data['EventType']['type']);
		$this->validate = array_merge ($this->validate, $event_obj->configurationFieldsValidation($this->data));
		return true;
	}

	function beforeSave() {
		$event_obj = AppController::_getComponent ('EventType', $this->data['EventType']['type']);
		// Pull out the custom configuration fields
		$custom = array_intersect_key($this->data['Event'], array_flip ($event_obj->configurationFields()));
		$this->data['Event']['custom'] = serialize($custom);
		return true;
	}

	function _afterFind($record) {
		if (!empty ($record['Event']['custom'])) {
			$custom = unserialize ($record['Event']['custom']);
			unset ($record['Event']['custom']);
			if (!empty ($custom)) {
				$record['Event'] = array_merge ($record['Event'], $custom);
			}
		}
		return $record;
	}

	// We need to intercept this to find the event type, so we can correctly
	// process the intercepted getColumnType calls.
	function set($one, $two = null) {
		if (array_key_exists ('EventType', $one)) {
			$this->event_obj = AppController::_getComponent ('EventType', $one['EventType']['type']);
		}
		$data = parent::set($one, $two);
		if (array_key_exists ('EventType', $one)) {
			unset ($this->event_obj);
		}
		return $data;
	}

	// We may need to intercept getColumnType calls in order to allow deconstruct
	// to properly handle virtual fields (dates, for example).  Since we don't know
	// what field names might exist, we pass it off to the event type's object.
	function getColumnType($column) {
		if (isset ($this->event_obj) && method_exists ($this->event_obj, 'getColumnType')) {
			$type = $this->event_obj->getColumnType($column);
			if ($type) {
				return $type;
			}
		}
		return parent::getColumnType($column);
	}
}
?>
