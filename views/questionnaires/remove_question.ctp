<?php
if (isset($cannot)) {
	echo $this->Html->scriptBlock ("alert('This question has responses saved, and cannot be removed for historical purposes. You can deactivate it instead, so it will no longer be shown for new registrations.')");
} else {
	if ($success) {
		echo $this->Html->scriptBlock ("jQuery('#$id').remove();");
	} else {
		echo $this->Html->scriptBlock ("alert('Failed to remove this question.')");
	}
}

// Output the event handler code for the links
echo $this->Js->writeBuffer();
?>
