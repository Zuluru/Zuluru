<?php
if ($success) {
	$content = $this->Js->link(__('Close', true),
			array('action' => 'close', 'field' => $id),
			array('update' => "#temp_update")
	);
	echo $this->Html->scriptBlock ("jQuery('#span_$id').html('$content')");
} else {
	$field = Configure::read('ui.field');
	echo $this->Html->scriptBlock ("alert('Failed to open $field \'$name\'.')");
}

// Output the event handler code for the links
echo $this->Js->writeBuffer();
?>
