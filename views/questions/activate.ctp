<?php
if ($success) {
	$content = $this->Js->link(__('Deactivate', true),
			array('action' => 'deactivate', 'question' => $question, 'id' => $id),
			array('update' => "#temp_update")
	);
	echo $this->Html->scriptBlock ("jQuery('#$id').html('$content')");
} else {
	echo $this->Html->scriptBlock ("alert('Failed to activate question \'$name\'.')");
}

// Output the event handler code for the links
echo $this->Js->writeBuffer();
?>
