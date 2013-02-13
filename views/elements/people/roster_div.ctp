<?php // This is required on every page where the roster change popup is used ?>
<div id="roster_options" style="display: none;">
<?php
$positions = Configure::read('options.roster_position');
foreach ($positions as $key => $position) {
	echo $this->Html->tag('div', __($position, true), array('id' => "roster_option_$key"));
}
?>
</div>

<?php
$this->ZuluruHtml->script ('roster', array('inline' => false));
?>
