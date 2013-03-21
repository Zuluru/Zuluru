<?php // This is required on every page where the attendance change popup is used ?>
<div id="attendance_options" style="display: none;">
<?php
$statuses = Configure::read('attendance');
foreach ($statuses as $key => $status) {
	echo $this->Html->tag('div', __($status, true), array('id' => "attendance_option_$key"));
}
echo $this->Html->tag('div', __('Comment', true), array('id' => "attendance_comment"));
?>
</div>
<div id="attendance_comment_div" style="display: none;" title="<?php __('Attendance comment'); ?>">
<p id="comment_to_captain"><?php __('If you want to add a comment for your captain, do so here.'); ?></p>
<p id="comment_to_player"><?php __('If you want to add a personal note to the player, do so here. To include no note with this invitation, leave this blank, but click "Save". "Cancel" will abort the invitation entirely.'); ?></p>
<br /><?php
echo $this->Form->input('comment', array(
		'label' => false,
		'size' => 70,
));
?>
</div>

<?php
$this->ZuluruHtml->script ('attendance', array('inline' => false));
?>
