<?php
$this->Html->addCrumb (__('Teams', true));
if (isset ($add)) {
	$this->Html->addCrumb (__('Create', true));
} else {
	$this->Html->addCrumb ($this->data['Team']['name']);
	$this->Html->addCrumb (__('Edit', true));
}
?>

<div class="teams form">
<?php echo $this->Form->create('Team', array('url' => $this->here));?>
	<fieldset>
 		<legend><?php __('Team Details'); ?></legend>
	<?php
		if (!isset ($add)) {
			echo $this->Form->input('id');
		}
		echo $this->Form->input('name', array(
			'after' => ' ' . $this->ZuluruHtml->help(array('action' => 'teams', 'edit', 'name')) .
				$this->Html->para (null, __('The full name of your team.', true)),
		));
		echo $this->Form->input('website', array(
			'after' => $this->Html->para (null, __('Your team\'s website, if you have one.', true)),
		));
		echo $this->Form->input('shirt_colour', array(
			'after' => ' ' . $this->ZuluruHtml->help(array('action' => 'teams', 'edit', 'shirt_colour')) .
				$this->Html->para (null, __('Shirt colour of your team. If you don\'t have team shirts, pick \'light\' or \'dark\'.', true)),
		));
		if ($is_admin) {
			// TODO: populate with possibilities from fields table
			echo $this->Form->input('home_field', array(
				'after' => $this->Html->para (null, __('Home field, if applicable.', true)),
			));
		}
		if (Configure::read('feature.region_preference')) {
			// TODO: populate with possibilities from regions table
			echo $this->Form->input('region_preference', array(
				'after' => $this->Html->para (null, __('Area of city where you would prefer to play.', true)),
			));
		}
		echo $this->Form->input('open_roster', array(
			'after' => ' ' . $this->ZuluruHtml->help(array('action' => 'teams', 'edit', 'roster_status')) .
				$this->Html->para (null, __('If the team roster is open, others can request to join; otherwise, only the captain can add players.', true)),
		));
		echo $this->Form->input('track_attendance', array(
			'after' => ' ' . $this->ZuluruHtml->help(array('action' => 'teams', 'edit', 'track_attendance')) .
				$this->Html->para (null, __('If selected, the system will help you to monitor attendance on a game-to-game basis.', true)),
			'onclick' => 'attendanceCheckboxChanged()',
		));
	?>
		<fieldset id="AttendanceDetails">
	<?php
		echo $this->Form->input('attendance_reminder', array(
			'size' => 1,
			'after' => $this->Html->para (null, __('Reminder emails will be sent to players that have not finalized their attendance this many days before the game. 0 means the day of the game, -1 will disable these reminders.', true)),
		));
		echo $this->Form->input('attendance_summary', array(
			'size' => 1,
			'after' => $this->Html->para (null, __('Attendance summary emails will be sent to captains this many days before the game. 0 means the day of the game, -1 will disable these summaries.', true)),
		));
		echo $this->Form->input('attendance_notification', array(
			'size' => 1,
			'after' => $this->Html->para (null, __('Emails notifying captains about changes in attendance status will be sent starting this many days before the game. 0 means the day of the game, -1 will disable these notifications. You will never receive notifications about any changes that happen before this time.', true)),
		));
	?>
		</fieldset>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>

<?php
echo $this->Html->scriptBlock("
function attendanceCheckboxChanged() {
	if ($('#TeamTrackAttendance').attr('checked')) {
		$('#AttendanceDetails').css('display', '');
	} else {
		$('#AttendanceDetails').css('display', 'none');
	}
}
");
$this->Js->buffer('attendanceCheckboxChanged();');
?>