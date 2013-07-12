<?php
class Pool extends AppModel {
	var $name = 'Pool';
	var $displayField = 'name';
	var $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
			),
		),
	);

	var $belongsTo = array(
		'Division' => array(
			'className' => 'Division',
			'foreignKey' => 'division_id',
		),
	);

	var $hasMany = array(
		'PoolsTeam' => array(
			'className' => 'PoolsTeam',
			'foreignKey' => 'pool_id',
			'dependent' => true,
		),
		'Game' => array(
			'className' => 'Game',
			'foreignKey' => 'pool_id',
			'dependent' => true,
		),
	);

	var $hasAndBelongsToMany = array(
		'Team' => array(
			'className' => 'Team',
			'joinTable' => 'pools_teams',
			'foreignKey' => 'pool_id',
			'associationForeignKey' => 'team_id',
			'unique' => true,
		),
	);

	function _dependency($pool) {
		if (!empty($pool['dependency_pool_id'])) {
			if (!array_key_exists('DependencyPool', $pool)) {
				trigger_error('Missing dependency information', E_USER_ERROR);
			}
			if ($pool['DependencyPool']['type'] == 'crossover') {
				return ($pool['dependency_id'] == 1 ? 'winner' : 'loser') . ' of ' . $pool['DependencyPool']['name'];
			} else {
				return ordinal($pool['dependency_id']) . ' in pool ' . $pool['DependencyPool']['name'];
			}
		} else if (!empty($pool['dependency_ordinal'])) {
			return ordinal($pool['dependency_id']) . ' among ' . ordinal($pool['dependency_ordinal']) . ' place teams';
		} else {
			return sprintf (__('%s seed', true), ordinal($pool['dependency_id']));
		}
	}
}
?>