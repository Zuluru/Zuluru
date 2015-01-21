<?php
if ($success) {
	$content = $this->Js->link(__('Activate', true),
			array('action' => 'activate', 'group' => $group, 'id' => $id),
			array('update' => "#temp_update")
	);
	echo $this->Html->scriptBlock ("jQuery('#$id').html('$content')");
} else {
	echo $this->Html->scriptBlock ("alert('Failed to deactivate group \'$name\'.')");
}

// Output the event handler code for the links
echo $this->Js->writeBuffer();
?>
