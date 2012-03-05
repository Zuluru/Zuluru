<p>The "edit facility" page is used to update details of your facilities.</p>
<p>The "create facility" page is essentially identical to this page.</p>
<?php
echo $this->element('help/topics', array(
		'section' => 'facilities/edit',
		'topics' => array(
			'name',
			'code',
			'is_open',
			'location_street' => 'Address',
			'driving_directions',
			'parking_details',
			'transit_directions',
			'biking_directions',
			'washrooms',
			'public_instructions',
			'site_instructions',
			'sponsor',
		),
));
?>
