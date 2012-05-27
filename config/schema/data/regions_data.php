<?php
// You can safely remove any of these that don't apply, add any new ones that you need,
// or change the names to match geographic areas in your city. Currently, fields can only
// be in ONE region, so make sure that the regions you define do not overlap.
class RegionsData {

	public $table = 'regions';

	public $records = array(
		array(
			'name' => 'North',
		),
		array(
			'name' => 'South',
		),
		array(
			'name' => 'East',
		),
		array(
			'name' => 'West',
		),
	);

}
?>
