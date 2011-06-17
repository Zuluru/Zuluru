<?php
class Field extends AppModel {
	var $name = 'Field';
	var $displayField = 'name';
	var $validate = array(
		'num' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'required' => false,
				'allowEmpty' => false,
				'message' => 'Field number cannot be blank',
			),
		),
		'rating' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.field_rating'),
				'message' => 'Select a rating from the list',
			),
		),
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'required' => false,
				'allowEmpty' => false,
				'message' => 'Name cannot be empty',
			),
		),
		'code' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'required' => false,
				'allowEmpty' => false,
				'message' => 'Code cannot be empty',
			),
		),
		'location_province' => array(
			'inquery' => array(
				'rule' => array('inquery', 'Province', 'name'),
				'required' => false,
				'message' => 'Select a province from the list',
			),
		),
		'location_url' => array(
			'url' => array(
				'rule' => array('url'),
				'required' => false,
				'allowEmpty' => true,
				'message' => 'Must be a valid URL, if specified',
			),
		),
		'layout_url' => array(
			'url' => array(
				'rule' => array('url'),
				'required' => false,
				'allowEmpty' => true,
				'message' => 'Must be a valid URL, if specified',
			),
		),
	);

	var $belongsTo = array(
		'ParentField' => array(
			'className' => 'Field',
			'foreignKey' => 'parent_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Region' => array(
			'className' => 'Region',
			'foreignKey' => 'region_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	var $hasMany = array(
		'ChildField' => array(
			'className' => 'Field',
			'foreignKey' => 'parent_id',
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
		'GameSlot' => array(
			'className' => 'GameSlot',
			'foreignKey' => 'field_id',
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

	function _afterFind ($record) {
		if (array_key_exists ('ParentField', $record) && !empty ($record['ParentField'])) {
			$parent = $record['ParentField'];
		} else if (array_key_exists ($this->alias, $record) && array_key_exists ('ParentField', $record[$this->alias]) && !empty ($record[$this->alias]['ParentField'])) {
			$parent = $record[$this->alias]['ParentField'];
		} else {
			$parent = array();
		}

		foreach ($parent as $key => $value) {
			if (array_key_exists ($key, $record[$this->alias])) {
				// For real fields, the array key will exist but the value might be empty
				if (empty ($record[$this->alias][$key])) {
					$record[$this->alias][$key] = $value;
				}
			} else {
				// If the array key doesn't exist, it's a related model and we want it moved up
				$record[$key] = $value;
			}
		}

		if (array_key_exists ('num', $record[$this->alias]) && !empty ($record[$this->alias]['num'])) {
			$record[$this->alias]['long_name'] = "{$record[$this->alias]['name']} {$record[$this->alias]['num']}";
		} else {
			$record[$this->alias]['long_name'] = $record[$this->alias]['name'];
		}

		// TODO: Determine this automatically?
		$season = 'summer';
		$permit_dir = join(DS, array(
				Configure::read('folders.league_base'),
				$season, 'current', 'permits'));

		// Auto-detect the permit URLs
		$record[$this->alias]['permit_url'] = '';
		if (array_key_exists ('code', $record[$this->alias]) && !empty($record[$this->alias]['code']) && is_dir($permit_dir)) {
			if ($dh = opendir($permit_dir)) {
				while (($file = readdir($dh)) !== false) {
					if (fnmatch ($record[$this->alias]['code'] . '*', $file) ) {
						$record[$this->alias]['permit_name'] = $file;
						$record[$this->alias]['permit_url'] = Configure::read('urls.league_base') . "/$season/current/permits/$file";
					}
				}
			}
		}

		return $record;
	}

	function readAtSite ($id, $parent = null, $conditions = array(), $open = true) {
		$this->recursive = -1;
		if ($parent != null) {
			$conditions = array_merge (array(
					'OR' => array(
						'Field.parent_id' => $parent,
						'Field.id' => $parent,
					),
					'Field.id !=' => $id,
			), $conditions);
		} else {
			$conditions = array_merge (array(
					'Field.parent_id' => $id,
			), $conditions);
		}
		// Either true or false will be passed in, anything else (e.g. null) means
		// don't include any condition on this, which will return all fields.
		if ($open === true || $open === false) {
			$conditions['Field.is_open'] = $open;
		}

		return $this->find('all', array(
				'conditions' => $conditions,
				'order' => 'Field.num',
		));
	}
}
?>
