<?php
if (Configure::read('feature.affiliates')) {
	if ($affiliate) {
		echo $this->Html->para('warning-message', sprintf(__('You are editing %s settings. You should update only those that differ from the default. To use the default for text fields, simply leave the field blank.', true), $affiliates[$affiliate]));
	} else {
		echo $this->Html->para('warning-message', __('You are editing the global system settings. These will be used for any affiliate that does not override them.', true));
	}
}
?>