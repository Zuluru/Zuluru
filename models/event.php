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
			'isunique' => array(
				'rule' => array('isUnique'),
				'message' => 'An event with that name already exists.',
			),
		),
		'affiliate_id' => array(
			'inlist' => array(
				'rule' => array('inquery', 'Affiliate', 'id'),
				'message' => 'You must select a valid affiliate.',
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
		'division_id' => array(
			'numeric' => array(
				'rule' => array('inquery', 'Division', 'id'),
				'required' => false,
				'allowEmpty' => true,
				'message' => 'You must select a valid division.',
			),
		),
	);

	var $belongsTo = array(
		'EventType' => array(
			'className' => 'EventType',
			'foreignKey' => 'event_type_id',
		),
		'Questionnaire' => array(
			'className' => 'Questionnaire',
			'foreignKey' => 'questionnaire_id',
		),
		'Division' => array(
			'className' => 'Division',
			'foreignKey' => 'division_id',
		),
		'Affiliate' => array(
			'className' => 'Affiliate',
			'foreignKey' => 'affiliate_id',
		),
	);

	var $hasMany = array(
		'Price' => array(
			'className' => 'Price',
			'foreignKey' => 'event_id',
			'dependent' => true,
		),
		'Preregistration' => array(
			'className' => 'Preregistration',
			'foreignKey' => 'event_id',
			'dependent' => false,
		),
		'Registration' => array(
			'className' => 'Registration',
			'foreignKey' => 'event_id',
			'dependent' => false,
		),
	);

	var $hasAndBelongsToMany = array(
		'Predecessor' => array(
			'className' => 'Event',
			'joinTable' => 'events_connections',
			'foreignKey' => 'event_id',
			'associationForeignKey' => 'connected_event_id',
			'unique' => true,
			'conditions' => array('connection' => EVENT_PREDECESSOR),
			'order' => array('Predecessor.event_type_id', 'Predecessor.open', 'Predecessor.close', 'Predecessor.id'),
		),
		'Successor' => array(
			'className' => 'Event',
			'joinTable' => 'events_connections',
			'foreignKey' => 'event_id',
			'associationForeignKey' => 'connected_event_id',
			'unique' => true,
			'conditions' => array('connection' => EVENT_SUCCESSOR),
			'order' => array('Successor.event_type_id', 'Successor.open', 'Successor.close', 'Successor.id'),
		),
		'Alternate' => array(
			'className' => 'Event',
			'joinTable' => 'events_connections',
			'foreignKey' => 'event_id',
			'associationForeignKey' => 'connected_event_id',
			'unique' => true,
			'conditions' => array('connection' => EVENT_ALTERNATE),
			'order' => array('Alternate.event_type_id', 'Alternate.open', 'Alternate.close', 'Alternate.id'),
		),
		'PredecessorTo' => array(
			'className' => 'Event',
			'joinTable' => 'events_connections',
			'foreignKey' => 'connected_event_id',
			'associationForeignKey' => 'event_id',
			'unique' => true,
			'conditions' => array('connection' => EVENT_PREDECESSOR),
			'order' => array('PredecessorTo.event_type_id', 'PredecessorTo.open', 'PredecessorTo.close', 'PredecessorTo.id'),
		),
		'SuccessorTo' => array(
			'className' => 'Event',
			'joinTable' => 'events_connections',
			'foreignKey' => 'connected_event_id',
			'associationForeignKey' => 'event_id',
			'unique' => true,
			'conditions' => array('connection' => EVENT_SUCCESSOR),
			'order' => array('SuccessorTo.event_type_id', 'SuccessorTo.open', 'SuccessorTo.close', 'SuccessorTo.id'),
		),
		'AlternateTo' => array(
			'className' => 'Event',
			'joinTable' => 'events_connections',
			'foreignKey' => 'connected_event_id',
			'associationForeignKey' => 'event_id',
			'unique' => true,
			'conditions' => array('connection' => EVENT_ALTERNATE),
			'order' => array('AlternateTo.event_type_id', 'AlternateTo.open', 'AlternateTo.close', 'AlternateTo.id'),
		),
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
		if (array_key_exists('EventType', $this->data)) {
			$event_obj = AppController::_getComponent ('EventType', $this->data['EventType']['type']);
			$this->validate = array_merge ($this->validate, $event_obj->configurationFieldsValidation($this->data));
		}
		return true;
	}

	function beforeSave() {
		if (array_key_exists('EventType', $this->data)) {
			$event_obj = AppController::_getComponent ('EventType', $this->data['EventType']['type']);
			// Pull out the custom configuration fields
			$custom = array_intersect_key($this->data['Event'], array_flip ($event_obj->configurationFields()));
			$this->data['Event']['custom'] = serialize($custom);
		}
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

	function affiliate($id) {
		return $this->field('affiliate_id', array('Event.id' => $id));
	}
}
?>
