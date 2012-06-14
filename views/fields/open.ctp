<?php
if ($success) {
	$content = $this->Js->link(__('Close', true),
			array('action' => 'close', 'field' => $field, 'id' => $id),
			array('update' => "#temp_update")
	);
	echo $this->Html->scriptBlock ("$('#$id').html('$content')");
} else {
	$field = Configure::read('ui.field');
	echo $this->Html->scriptBlock ("alert('Failed to open $field \'$name\'.')");
}

// Output the event handler code for the links
echo $this->Js->writeBuffer();
?>
