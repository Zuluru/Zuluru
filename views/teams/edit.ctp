<?php
$this->Html->addCrumb (__('Teams', true));
if (isset ($add)) {
	$this->Html->addCrumb (__('Create', true));
} else {
	$this->Html->addCrumb ($this->Form->value('Team.name'));
	$this->Html->addCrumb (__('Edit', true));
}
?>

<div class="teams form">
<?php echo $this->Form->create('Team', array('url' => Router::normalize($this->here)));?>
	<fieldset>
 		<legend><?php __('Team Details'); ?></legend>
	<?php
		if (!isset ($add)) {
			echo $this->Form->input('id');
		}
		echo $this->ZuluruForm->input('name', array(
			'after' => $this->Html->para (null, __('The full name of your team.', true)),
		));
		echo $this->ZuluruForm->input('short_name', array(
			'after' => $this->Html->para (null, __('A short name for your team, if you have one.', true)),
		));

		if (isset ($add)) {
			echo $this->ZuluruForm->input('affiliate_id', array(
				'options' => $affiliates,
				'hide_single' => true,
				'empty' => '---',
			));
		}

		if (Configure::read('feature.shirt_colour')) {
			echo $this->ZuluruForm->input('shirt_colour', array(
				'after' => $this->Html->para (null, __('Shirt colour of your team. If you don\'t have team shirts, pick \'light\' or \'dark\'.', true)),
			));
		}

		if (Configure::read('feature.attendance')):
			echo $this->ZuluruForm->input('track_attendance', array(
				'after' => $this->Html->para (null, __('If selected, the system will help you to monitor attendance on a game-to-game basis.', true)),
				'onclick' => 'attendanceCheckboxChanged()',
			));
		?>
		<fieldset id="AttendanceDetails">
		<?php
			echo $this->ZuluruForm->input('attendance_reminder', array(
				'size' => 1,
				'after' => $this->Html->para (null, __('Reminder emails will be sent to players that have not finalized their attendance this many days before the game. 0 means the day of the game, -1 will disable these reminders.', true)),
			));
			echo $this->ZuluruForm->input('attendance_summary', array(
				'size' => 1,
				'after' => $this->Html->para (null, __('Attendance summary emails will be sent to captains this many days before the game. 0 means the day of the game, -1 will disable these summaries.', true)),
			));
			echo $this->ZuluruForm->input('attendance_notification', array(
				'size' => 1,
				'after' => $this->Html->para (null, __('Emails notifying captains about changes in attendance status will be sent starting this many days before the game. 0 means the day of the game, -1 will disable these notifications. You will never receive notifications about any changes that happen before this time.', true)),
			));
		?>
		</fieldset>
		<?php
		endif;

		if (Configure::read('feature.region_preference')) {
			echo $this->ZuluruForm->input('region_preference', array(
				'after' => $this->Html->para (null, __('Area of city where you would prefer to play.', true)),
				'options' => $regions,
				'empty' => __('No preference', true),
			));
		}

		echo $this->ZuluruForm->input('open_roster', array(
			'after' => $this->Html->para (null, __('If the team roster is open, others can request to join; otherwise, only the captain can add players.', true)),
		));

		if (Configure::read('feature.urls')) {
			echo $this->ZuluruForm->input('website', array(
				'after' => $this->Html->para (null, __('Your team\'s website, if you have one.', true)),
			));
		}

		if (Configure::read('feature.flickr')) {
			if ($is_admin) {
				echo $this->ZuluruForm->input('flickr_ban', array(
					'after' => $this->Html->para (null, __('If selected, this team\'s Flickr slideshow will no longer be shown. This is for use if teams repeatedly violate this site\'s terms of service.', true)),
				));
			} else if ($this->Form->value('Team.flickr_ban')) {
				echo $this->Html->para('warning-message', __('Your team has been banned from using the Flickr slideshow. Contact an administrator if you believe this was done in error or would like to request a review.', true));
			}
			if ($is_admin || !$this->Form->value('Team.flickr_ban')) {
				echo $this->ZuluruForm->input('flickr_user', array(
					'after' => $this->Html->para (null, __('The URL for your photo set will be something like http://www.flickr.com/photos/abcdef/sets/12345678901234567/. abcdef is your user name.', true)),
				));
				echo $this->ZuluruForm->input('flickr_set', array(
					'after' => $this->Html->para (null, __('The URL for your photo set will be something like http://www.flickr.com/photos/abcdef/sets/12345678901234567/. 12345678901234567 is your set number.', true)),
				));
			}
		}

		if (Configure::read('feature.twitter')) {
			echo $this->ZuluruForm->input('twitter_user', array(
				'after' => $this->Html->para (null, __('Do NOT include the @; it will be automatically added for you.', true)),
			));
		}

		if ($is_admin && Configure::read('feature.home_field')) {
			echo $this->ZuluruForm->input('home_field', array(
				'label' => sprintf(__('Home %s', true), Configure::read('sport.field_cap')),
				'after' => $this->Html->para (null, sprintf(__('Home %s, if applicable.', true), Configure::read('sport.field'))),
				'options' => $fields,
				'empty' => sprintf(__('No home %s', true), Configure::read('sport.field')),
			));
		}
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>

<?php
if (Configure::read('feature.attendance')) {
	echo $this->Html->scriptBlock("
function attendanceCheckboxChanged() {
	if (jQuery('#TeamTrackAttendance').attr('checked')) {
		jQuery('#AttendanceDetails').css('display', '');
	} else {
		jQuery('#AttendanceDetails').css('display', 'none');
	}
}
");
	$this->Js->buffer('attendanceCheckboxChanged();');
}
?>
