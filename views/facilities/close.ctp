<?php
if ($success) {
	$content = $this->Js->link(__('Open', true),
			array('action' => 'open', 'facility' => $id),
			array('update' => "#temp_update")
	);
	echo $this->Html->scriptBlock ("jQuery('#span_$id').html('$content')");
} else {
	$fields = Configure::read('ui.fields');
	$alert = printf(__('Failed to close facility \'%s\' or one of its %s.', true), addslashes($name), $fields);
	echo $this->Html->scriptBlock ("alert('$alert')");
}

// Output the event handler code for the links
echo $this->Js->writeBuffer();
?>
