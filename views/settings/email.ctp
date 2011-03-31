<?php
$this->Html->addCrumb (__('Settings', true));
$this->Html->addCrumb (__('Email', true));
?>

<div class="settings form">
<?php echo $this->Form->create('Settings', array('url' => array('email')));?>
	<fieldset>
 		<legend><?php __('Sender'); ?></legend>
	<?php
	echo $this->element ('setting/input', array(
		'category' => 'email',
		'name' => 'admin_name',
		'options' => array(
			'after' => 'The name (or descriptive role) of the system administrator. Mail from Zuluru will come from this name.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'email',
		'name' => 'admin_email',
		'options' => array(
			'after' => 'The e-mail address of the system administrator. Mail from Zuluru will come from this address.',
		),
	));
	if (Configure::read('scoring.incident_reports')) {
		echo $this->element ('setting/input', array(
			'category' => 'email',
			'name' => 'incident_report_email',
			'options' => array(
				'after' => 'The e-mail address to send incident reports to, if enabled.',
			),
		));
	}
	?>
	</fieldset>

	<fieldset>
 		<legend><?php __('Account Management'); ?></legend>
	<?php
	echo $this->element ('setting/input', array(
		'category' => 'email',
		'name' => 'approved_subject',
		'options' => array(
			'label' => 'Subject of account approval e-mail',
			'after' => 'Customize the subject of your approval e-mail, which is sent after account is approved. Available variables are: %username, %site, %url.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'email',
		'name' => 'approved_player_body',
		'options' => array(
			'type' => 'textarea',
			'label' => 'Body of account approval e-mail (player)',
			'after' => 'Customize the body of your approval e-mail, to be sent to players after accounts are approved. Available variables are: %fullname, %memberid, %adminname, %username, %site, %url.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'email',
		'name' => 'approved_visitor_body',
		'options' => array(
			'type' => 'textarea',
			'label' => 'Body of account approval e-mail (visitor)',
			'after' => 'Customize the body of your approval e-mail, to be sent to a non-player visitor after account is approved. Available variables are: %fullname, %adminname, %username, %site, %url.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'email',
		'name' => 'delete_duplicate_subject',
		'options' => array(
			'label' => 'Subject of duplicate account deletion e-mail',
			'after' => 'Customize the subject of your account deletion mail, sent to a user who has created a duplicate account. Available variables are: %site.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'email',
		'name' => 'delete_duplicate_body',
		'options' => array(
			'type' => 'textarea',
			'label' => 'Body of duplicate account deletion e-mail',
			'after' => 'Customize the body of your account deletion e-mail, sent to a user who has created a duplicate account. Available variables are: %fullname, %adminname, %existingusername, %existingemail, %site, %passwordurl.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'email',
		'name' => 'merge_duplicate_subject',
		'options' => array(
			'label' => 'Subject of duplicate account merge e-mail',
			'after' => 'Customize the subject of your account merge mail, sent to a user who has created a duplicate account. Available variables are: %site.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'email',
		'name' => 'merge_duplicate_body',
		'options' => array(
			'type' => 'textarea',
			'label' => 'Body of duplicate account merge e-mail',
			'after' => 'Customize the body of your account merge e-mail, sent to a user who has created a duplicate account. Available variables are: %fullname, %adminname, %existingusername, %existingemail, %site, %passwordurl.',
		),
	));
	if (Configure::read('feature.registration')) {
		echo $this->element ('setting/input', array(
			'category' => 'email',
			'name' => 'member_letter_subject',
			'options' => array(
				'label' => 'Subject of membership letter e-mail',
				'after' => 'Customize the subject of your membership letter e-mail, which is sent annually after membership is paid for. Available variables are: %fullname, %firstname, %lastname, %site, %year.',
			),
		));
		echo $this->element ('setting/input', array(
			'category' => 'email',
			'name' => 'member_letter_body',
			'options' => array(
				'type' => 'textarea',
				'label' => 'Body of membership letter e-mail (player)',
				'after' => 'Customize the body of your membership letter e-mail, which is sent annually after membership is paid for. If registrations are disabled, or this field is empty, no letters will be sent. Available variables are: %fullname, %firstname, %lastname, %adminname, %site, %year.',
			),
		));
	}
	if (Configure::read ('feature.manage_accounts')) {
		echo $this->element ('setting/input', array(
			'category' => 'email',
			'name' => 'password_reset_subject',
			'options' => array(
				'label' => 'Subject of password reset e-mail',
				'after' => 'Customize the subject of your password reset e-mail, which is sent when a user requests a password reset. Available variables are: %site.',
			),
		));
		echo $this->element ('setting/input', array(
			'category' => 'email',
			'name' => 'password_reset_body',
			'options' => array(
				'type' => 'textarea',
				'label' => 'Body of password reset e-mail',
				'after' => 'Customize the body of your password reset e-mail, which is sent when a user requests a password reset. Available variables are: %fullname, %adminname, %username, %password, %site, %url.',
			),
		));
	}
	?>
	</fieldset>

	<fieldset>
 		<legend><?php __('Scoring'); ?></legend>
	<?php
	echo $this->element ('setting/input', array(
		'category' => 'email',
		'name' => 'score_reminder_subject',
		'options' => array(
			'label' => 'Subject of score reminder e-mail',
			'after' => 'Customize the subject of your score reminder mail, sent to captains when they have not submitted a score in a timely fashion. Available variables are: %site, %fullname, %team, %opponent, %league, %gamedate, %scoreurl, %adminname.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'email',
		'name' => 'score_reminder_body',
		'options' => array(
			'type' => 'textarea',
			'label' => 'Body of score reminder e-mail',
			'after' => 'Customize the body of your score reminder e-mail, sent to captains when they have not submitted a score in a timely fashion. Available variables are: %site, %fullname, %team, %opponent, %league, %gamedate, %scoreurl, %adminname.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'email',
		'name' => 'approval_notice_subject',
		'options' => array(
			'label' => 'Subject of approval notice e-mail',
			'after' => 'Customize the subject of your approval notice mail, sent to captains when a game has been approved without a score submission from them. Available variables are: %site, %fullname, %team, %opponent, %league, %gamedate, %scoreurl, %adminname.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'email',
		'name' => 'approval_notice_body',
		'options' => array(
			'type' => 'textarea',
			'label' => 'Body of approval notice e-mail',
			'after' => 'Customize the body of your approval notice e-mail, sent to captains when a game has been approved without a score submission from them. Available variables are: %site, %fullname, %team, %opponent, %league, %gamedate, %scoreurl, %adminname.',
		),
	));
	?>
	</fieldset>

	<fieldset>
 		<legend><?php __('Photo Uploads'); ?></legend>
	<?php
	echo $this->element ('setting/input', array(
		'category' => 'email',
		'name' => 'photo_approved_subject',
		'options' => array(
			'label' => 'Subject of photo approval e-mail',
			'after' => 'Customize the subject of your photo approval mail, sent to a user whose photo has been approved. Available variables are: %site, %fullname, %adminname.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'email',
		'name' => 'photo_approved_body',
		'options' => array(
			'type' => 'textarea',
			'label' => 'Body of photo approval e-mail',
			'after' => 'Customize the body of your photo approval e-mail, sent to a user whose photo has been approved. Available variables are: %site, %fullname, %adminname.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'email',
		'name' => 'photo_deleted_subject',
		'options' => array(
			'label' => 'Subject of photo deletion e-mail',
			'after' => 'Customize the subject of your photo deleted mail, sent to a user whose photo has been deleted. Available variables are: %site, %fullname, %adminname.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'email',
		'name' => 'photo_deleted_body',
		'options' => array(
			'type' => 'textarea',
			'label' => 'Body of photo deletion e-mail',
			'after' => 'Customize the body of your photo deleted e-mail, sent to a user whose photo has been deleted. Available variables are: %site, %fullname, %adminname.',
		),
	));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
