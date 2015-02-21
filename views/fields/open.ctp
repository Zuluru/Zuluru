<?php
if ($success) {
	$content = $this->Js->link(__('Close', true),
			array('action' => 'close', 'field' => $id),
			array('update' => "#temp_update")
	);
	echo $this->Html->scriptBlock ("jQuery('#span_$id').html('$content')");
} else {
	$field = Configure::read('ui.field');
	$alert = printf(__('Failed to open %s \'%s\'.', true), $field, addslashes($name));
	echo $this->Html->scriptBlock ("alert('$alert')");
}

// Output the event handler code for the links
echo $this->Js->writeBuffer();
?>
