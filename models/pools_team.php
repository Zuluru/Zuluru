<?php
class PoolsTeam extends AppModel {
	var $name = 'PoolsTeam';
	var $displayField = 'alias';

	var $belongsTo = array(
		'Pool' => array(
			'className' => 'Pool',
			'foreignKey' => 'pool_id',
		),
		'DependencyPool' => array(
			'className' => 'Pool',
			'foreignKey' => 'dependency_pool_id',
		),
		'Team' => array(
			'className' => 'Team',
			'foreignKey' => 'team_id',
		)
	);
}
?>