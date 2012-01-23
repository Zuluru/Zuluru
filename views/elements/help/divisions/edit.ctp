<p>The "edit division" page is used to update details of your division. Only coordinators have permission to edit division details.</p>
<p>The "create division" page is essentially identical to this page.</p>
<?php
echo $this->element('help/topics', array(
		'section' => 'divisions/edit',
		'topics' => array(
			'name',
			'schedule_type',
			'rating_calculator',
			'current_round',
			'exclude_teams',
		),
));
?>
