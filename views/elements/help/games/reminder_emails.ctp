<p><?php
printf(__('When a team is using attendance tracking and enables reminder emails, by default these reminders will only be sent to players who have not yet indicated their attendance. If you would like to get a reminder of the game location and time even if you have set your attendance in advance, %s to enable this option.', true),
	$this->Html->link (__('edit your preferences', true), array('controller' => 'people', 'action' => 'preferences'))
);
?></p>
