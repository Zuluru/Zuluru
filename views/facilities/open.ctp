<?php
if ($success) {
	$content = $this->Js->link(__('Close', true),
			array('action' => 'close', 'facility' => $id),
			array('update' => "#temp_update")
	);
	echo $this->Html->scriptBlock ("jQuery('#span_$id').html('$content')");
} else {
	echo $this->Html->scriptBlock ("alert('Failed to open facility \'$name\'.')");
}

// Output the event handler code for the links
echo $this->Js->writeBuffer();
?>
