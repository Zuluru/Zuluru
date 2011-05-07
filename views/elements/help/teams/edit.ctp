<p>The "edit team" page is used to update details of your team. Only captains have permission to edit team details.</p>
<?php if (Configure::read('feature.registration')): ?>
<p>Since this system uses the <?php
echo $this->Html->link(__('registration system', true), array('controller' => 'events', 'action' => 'wizard')) . ' ' .
	$this->ZuluruHtml->iconLink('help_16.png',
			array('controller' => 'help', 'action' => 'registration'),
			array('alt' => __('Registration Help', true), 'title' => __('Registration Help', true)));
?>, teams are created during the registration process with some default values that you might want to alter.</p>
<?php else: ?>
<p>The "create team" page is essentially identical to this page.</p>
<?php endif; ?>
<?php
echo $this->element('help/topics', array(
		'section' => 'teams/edit',
		'topics' => array(
			'name',
			'shirt_colour',
			'roster_status',
			'track_attendance' => array(
				'image' => 'attendance_32.png',
			),
		),
));
?>
