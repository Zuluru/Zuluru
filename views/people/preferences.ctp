<?php
$this->Html->addCrumb (__('Preferences', true));
$this->Html->addCrumb ("{$person['first_name']} {$person['last_name']}");
?>

<div class="settings form">
<?php echo $this->Form->create('People', array('url' => Router::normalize($this->here)));?>
	<fieldset>
		<legend><?php __('Preferences'); ?></legend>
	<?php
	echo $this->element('settings/input', array(
		'person_id' => $id,
		'category' => 'personal',
		'name' => 'enable_ical',
		'options' => array(
			'label' => __('Enable Personal iCal Feed', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => $this->Html->tag('span', __('NOTE: By enabling this, you agree to make your personal schedule in iCal format available as public information (required for Google Calendar, etc. to be able to access the data.)', true), array('class' => 'highlight-message')),
		),
	));

	echo $this->element('settings/input', array(
		'person_id' => $id,
		'category' => 'personal',
		'name' => 'attendance_emails',
		'options' => array(
			'label' => __('Always Send Attendance Reminder Emails', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('Turn this on if you want to receive reminder emails (with game information) for games that you have already indicated your attendance for. Turn off if you only want emails when you have not yet set your attendance.', true) . ' ' .
					$this->Html->tag('span', __('NOTE: This applies only to teams with attendance tracking enabled.', true), array('class' => 'highlight-message')),
		),
	));

	$now = time() - Configure::read('timezone.adjust') * 60;

	$options = array('' => __('use system default', true));
	foreach (Configure::read('options.date_formats') as $format) {
		$options[$format] = date($format, $now);
	}
	echo $this->element('settings/input', array(
		'person_id' => $id,
		'category' => 'personal',
		'name' => 'date_format',
		'options' => array(
			'label' => __('Date Format', true),
			'type' => 'radio',
			'options' => $options,
			'after' => __('Select your preferred date format', true),
		),
	));

	$options = array('' => __('use system default', true));
	foreach (Configure::read('options.day_formats') as $format) {
		$options[$format] = date($format, $now);
	}
	echo $this->element('settings/input', array(
		'person_id' => $id,
		'category' => 'personal',
		'name' => 'day_format',
		'options' => array(
			'label' => __('Day Format', true),
			'type' => 'radio',
			'options' => $options,
			'after' => __('Select your preferred day format', true),
		),
	));

	$options = array('' => __('use system default', true));
	foreach (Configure::read('options.time_formats') as $format) {
		$options[$format] = date($format, $now);
	}
	echo $this->element('settings/input', array(
		'person_id' => $id,
		'category' => 'personal',
		'name' => 'time_format',
		'options' => array(
			'label' => __('Time Format', true),
			'type' => 'radio',
			'options' => $options,
			'after' => __('Select your preferred time format', true),
		),
	));

	$languages = Configure::read('available_translations');
	if (Configure::read('feature.language') && count($languages) > 1) {
		echo $this->element('settings/input', array(
			'person_id' => $id,
			'category' => 'personal',
			'name' => 'language',
			'options' => array(
				'label' => __('Preferred Language', true),
				'type' => 'select',
				'options' => $languages,
				'empty' => __('use system default', true),
			),
		));
	}

	if (Configure::read('feature.twitter')):
	?>
		<fieldset>
			<legend><?php __('Twitter'); ?></legend>
<?php
		if (!empty($person['twitter_token'])) {
			echo $this->Html->para(null, sprintf(__('You have authorized your account to post updates to Twitter. You can %s if you no longer want to tweet updates.', true),
				$this->Html->link(__('revoke this authorization', true), array('action' => 'revoke_twitter'))
			));
		} else {
			echo $this->Html->para(null, sprintf(__('This system can post certain updates to Twitter on your behalf. To enable this, you must %s. Note that nothing will ever be tweeted automatically; this authorization enables you to tweet directly from this site.', true),
				$this->Html->link(__('authorize Twitter to accept these tweets', true), array('action' => 'authorize_twitter'))
			));
		}
?>
		</fieldset>
<?php
	endif;
?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
