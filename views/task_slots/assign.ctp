<?php
$no = __('No', true);
$yes = __('Yes', true);

if (isset($error)) {
	echo $this->Html->scriptBlock ("alert('$error');");
	if (isset($reset)) {
		echo $this->Html->scriptBlock ("jQuery('#slot_{$id} select option[value=\"\"]').attr('selected', 'selected');");
	}
} else if ($person_id === '0') {
	echo $this->Html->scriptBlock ("
jQuery('#slot_{$id}').addClass('unpublished');
jQuery('#slot_{$id} td.approved').html('$no');
jQuery('#slot_{$id} td.approved_by').html('');
	");
} else {
	echo $this->Html->scriptBlock ("
jQuery('#slot_{$id}').removeClass('unpublished');
	");
	if (isset($approved_by)) {
		$approved_by = $this->element('people/block', array('person' => $approved_by));
		echo $this->Html->scriptBlock ("
jQuery('#slot_{$id} td.approved').html('$yes');
jQuery('#slot_{$id} td.approved_by').html('$approved_by');
		");
	} else {
		echo $this->Html->scriptBlock ("
jQuery('#slot_{$id} td.approved').html('$no');
jQuery('#slot_{$id} td.approved_by').html('');
		");
	}
	if (!$is_admin && !$is_manager) {
		$assigned_to = $this->element('people/block', array('person' => $person));
		echo $this->Html->scriptBlock ("
jQuery('#slot_{$id} td.assigned_to').html('$assigned_to');
		");
	}
}
?>
