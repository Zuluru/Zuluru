<?php
if ($success) {
	$content = $this->Js->link(__('Open', true),
			array('action' => 'open', 'facility' => $facility, 'id' => $id),
			array('update' => "#temp_update")
	);
	echo $this->Html->scriptBlock ("$('#$id').html('$content')");
} else {
	$fields = Configure::read('ui.fields');
	echo $this->Html->scriptBlock ("alert('Failed to close facility \'$name\' or one of its $fields.')");
}

// Output the event handler code for the links
echo $this->Js->writeBuffer();
?>
