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
				'message' => 'Number cannot be blank',
			),
		),
		'rating' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.field_rating'),
				'message' => 'Select a rating from the list',
			),
		),
		'surface' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.surface'),
				'required' => false,
				'message' => 'Select a playing surface from the list',
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
		'Facility' => array(
			'className' => 'Facility',
			'foreignKey' => 'facility_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	var $hasMany = array(
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
		if (!empty ($record['Facility'])) {
			$facility = $record['Facility'];
		} else if (!empty ($record[$this->alias]['Facility'])) {
			$facility = $record[$this->alias]['Facility'];
		}

		if (isset($facility)) {
			if (!empty ($record[$this->alias]['num'])) {
				if (!empty ($facility['name'])) {
					$record[$this->alias]['long_name'] = "{$facility['name']} {$record[$this->alias]['num']}";
				}
				if (!empty ($facility['code'])) {
					$record[$this->alias]['long_code'] = "{$facility['code']} {$record[$this->alias]['num']}";
				}
			} else {
				if (!empty ($facility['name'])) {
					$record[$this->alias]['long_name'] = $facility['name'];
				}
				if (!empty ($facility['code'])) {
					$record[$this->alias]['long_code'] = $facility['code'];
				}
			}

			// If we haven't read the "indoor" field, we don't need any of the permit info either
			if (array_key_exists('indoor', $record[$this->alias]) && !empty($facility['code'])) {
				$season = Inflector::slug(low(season($record[$this->alias]['indoor'])));
				$permit_dir = join(DS, array(
						Configure::read('folders.league_base'),
						$season, 'current', 'permits'));

				// Auto-detect the permit URLs
				$record[$this->alias]['permit_url'] = '';
				if (is_dir($permit_dir)) {
					if ($dh = opendir($permit_dir)) {
						while (($file = readdir($dh)) !== false) {
							if (fnmatch ($facility['code'] . '*', $file) ) {
								$record[$this->alias]['permit_name'] = $file;
								$record[$this->alias]['permit_url'] = Configure::read('urls.league_base') . "/$season/current/permits/$file";
							}
						}
					}
				}
			}
		}

		return $record;
	}

	function affiliate($id) {
		return $this->Facility->affiliate($this->field('facility_id', array('Field.id' => $id)));
	}
}
?>
