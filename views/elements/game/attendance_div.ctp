<?php // This is required on every page where the attendance change popup is used ?>
<div id="attendance_options" style="display: none;">
<?php
$statuses = Configure::read('attendance');
foreach ($statuses as $key => $status) {
	echo $this->Html->tag('div', __($status, true), array('id' => "attendance_option_$key"));
}
?>
</div>

<?php
$this->ZuluruHtml->script ('attendance', array('inline' => false));
?>
