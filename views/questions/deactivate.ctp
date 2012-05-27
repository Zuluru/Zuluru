<?php
if ($success) {
	$content = $this->Js->link(__('Activate', true),
			array('action' => 'activate', 'question' => $question, 'id' => $id),
			array('update' => "#temp_update")
	);
	echo $this->Html->scriptBlock ("$('#$id').html('$content')");
} else {
	echo $this->Html->scriptBlock ("alert('Failed to deactivate question \'$name\'.')");
}

// Output the event handler code for the links
echo $this->Js->writeBuffer();
?>
