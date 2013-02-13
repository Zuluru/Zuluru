<?php // This is required on every page where the shirt number change popup is used ?>
<div id="number_entry_div" style="display: none;" title="<?php __('Shirt Number'); ?>">
<p><?php __('Enter the new shirt number here, or leave blank to assign no shirt number.'); ?></p>
<br /><?php
echo $this->ZuluruForm->input('number', array(
		'label' => false,
		'type' => 'number',
		'size' => 6,
));
?>
</div>

<?php
$this->ZuluruHtml->script ('number', array('inline' => false));
?>
