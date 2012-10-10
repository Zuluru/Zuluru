<?php
$this->Html->addCrumb (__('Divisions', true));
$this->Html->addCrumb ($this->data['Division']['long_league_name']);
$this->Html->addCrumb (__('Add Teams', true));
?>

<div class="teams form">
<?php echo $this->Form->create('Team', array('url' => Router::normalize($this->here)));?>
	<?php
	echo $this->Form->hidden('Division.long_league_name');
	echo $this->Form->hidden('Division.id');
	echo $this->Form->hidden('0.division_id', array('value' => $this->data['Division']['id']));
	?>

	<fieldset>
 		<legend><?php __('Team Names'); ?></legend>
	<?php
	$colours = Configure::read('automatic_team_colours');
	echo $this->Html->para(null, sprintf(__('This can be used to create up to %d teams at once. To create less, simply leave those names blank.', true), count($colours)));
	foreach ($colours as $key => $colour) {
		$num = $key + 1;
		// Intentionally use Form, not ZuluruForm, to avoid multiple help instances
		echo $this->Form->input("$num.name", array(
			'label' => false,
		));
		if (Configure::read('feature.shirt_colour')) {
			echo $this->Form->hidden("$num.shirt_colour", array('value' => $colour));
		}
	}
	?>
	</fieldset>

	<fieldset>
 		<legend><?php __('Team Details'); ?></legend>
	<?php
		echo $this->ZuluruForm->input('0.open_roster', array(
			'after' => $this->Html->para (null, __('If the team roster is open, others can request to join; otherwise, only the captain can add players.', true)),
		));
		if (Configure::read('feature.attendance')) {
			echo $this->ZuluruForm->input('0.track_attendance', array(
				'after' => $this->Html->para (null, __('If selected, the system will help you to monitor attendance on a game-to-game basis.', true)),
				'onclick' => 'attendanceCheckboxChanged()',
			));
		}
	?>
	<?php if (Configure::read('feature.attendance')): ?>
		<fieldset id="AttendanceDetails">
	<?php
		echo $this->ZuluruForm->input('0.attendance_reminder', array(
			'size' => 1,
			'after' => $this->Html->para (null, __('Reminder emails will be sent to players that have not finalized their attendance this many days before the game. 0 means the day of the game, -1 will disable these reminders.', true)),
		));
		echo $this->ZuluruForm->input('0.attendance_summary', array(
			'size' => 1,
			'after' => $this->Html->para (null, __('Attendance summary emails will be sent to captains this many days before the game. 0 means the day of the game, -1 will disable these summaries.', true)),
		));
		echo $this->ZuluruForm->input('0.attendance_notification', array(
			'size' => 1,
			'after' => $this->Html->para (null, __('Emails notifying captains about changes in attendance status will be sent starting this many days before the game. 0 means the day of the game, -1 will disable these notifications. You will never receive notifications about any changes that happen before this time.', true)),
		));
	?>
		</fieldset>
	<?php endif; ?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>

<?php
if (Configure::read('feature.attendance')) {
	echo $this->Html->scriptBlock("
function attendanceCheckboxChanged() {
	if (jQuery('#Team0TrackAttendance').attr('checked')) {
		jQuery('#AttendanceDetails').css('display', '');
	} else {
		jQuery('#AttendanceDetails').css('display', 'none');
	}
}
");
	$this->Js->buffer('attendanceCheckboxChanged();');
}
?>