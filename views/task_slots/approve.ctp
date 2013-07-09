<?php
$no = __('No', true);
$yes = __('Yes', true);

if (isset($error)) {
	echo $this->Html->scriptBlock ("alert('$error');");
} else {
	$approved_by = $this->element('people/block', array('person' => $approved_by));
	echo $this->Html->scriptBlock ("
jQuery('#slot_{$id} td.approved').html('$yes');
jQuery('#slot_{$id} td.approved_by').html('$approved_by');
jQuery('#slot_{$id} .approve_link').remove();
	");
}
?>
