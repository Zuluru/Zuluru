<p>On the <?php echo $this->Html->link('My Profile -> Preferences', array('controller' => 'people', 'action' => 'preferences')); ?> page,
you can set a number of options which change the way the site works for you.</p>
<?php
echo $this->element('help/topics', array(
		'section' => 'games',
		'topics' => array(
			'personal_feed' => 'Enable Personal iCal Feed',
			'reminder_emails' => 'Always Send Attendance Reminder Emails',
			'date_time_format' => 'Date/Day/Time Format',
		),
		'compact' => true,
));
?>
