<?php
if ($success) {
	$content = $this->Js->link(__('Open', true),
			array('action' => 'open', 'field' => $field, 'id' => $id),
			array('update' => "#temp_update")
	);
	echo $this->Html->scriptBlock ("$('#$id').html('$content')");
} else {
	echo $this->Html->scriptBlock ("alert('Failed to close field \'$name\'.')");
}

// Output the event handler code for the links
echo $this->Js->writeBuffer();
?>
