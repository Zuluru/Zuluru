<?php // This is required on every page where the roster change popup is used ?>
<div id="roster_role_options" style="display: none;">
<?php
$roles = Configure::read('options.roster_role');
foreach ($roles as $key => $role) {
	echo $this->Html->tag('div', __($role, true), array('id' => "roster_role_option_$key"));
}
?>
</div>
<div id="roster_position_options" style="display: none;">
<?php
$positions = Configure::read('sport.positions');
foreach ($positions as $key => $position) {
	echo $this->Html->tag('div', __($position, true), array('id' => "roster_position_option_$key"));
}
?>
</div>

<?php
$this->ZuluruHtml->script ('roster', array('inline' => false));
?>
