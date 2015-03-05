<p><?php
printf(__('On the %s page, you can set a number of options which change the way the site works for you.', true),
	$this->Html->link(__('My Profile', true) . ' -> ' . __('Preferences', true), array('controller' => 'people', 'action' => 'preferences'))
); ?></p>
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
