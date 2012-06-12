<?php
if ($success) {
	echo $this->Html->scriptBlock ("$('#$row').remove()");
} else {
	echo $this->Html->scriptBlock ("alert('Failed to delete the document.')");
}
?>