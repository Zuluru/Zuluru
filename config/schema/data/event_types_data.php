<?php
class EventTypesData {

	public $table = 'event_types';

	public $records = array(
		array(
			'name' => 'Membership',
			'type' => 'membership',
		),
		array(
			'name' => 'Teams for Leagues',
			'type' => 'team',
		),
		array(
			'name' => 'Individuals for Leagues',
			'type' => 'generic',
		),
		array(
			'name' => 'Teams for Events',
			'type' => 'team',
		),
		array(
			'name' => 'Individuals for Events',
			'type' => 'generic',
		),
		array(
			'name' => 'Clinics',
			'type' => 'generic',
		),
		array(
			'name' => 'Social Events',
			'type' => 'generic',
		),
	);
}
?>
