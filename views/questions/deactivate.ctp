<?php
if ($success) {
	$content = $this->Js->link(__('Activate', true),
			array('action' => 'activate', 'question' => $question, 'id' => $id),
			array('update' => "#temp_update")
	);
	echo $this->Html->scriptBlock ("jQuery('#$id').html('$content')");
} else {
	$alert = printf(__('Failed to deactivate %s \'%s\'.', true), __('question', true), addslashes($name));
	echo $this->Html->scriptBlock ("alert('$alert')");
}

// Output the event handler code for the links
echo $this->Js->writeBuffer();
?>
