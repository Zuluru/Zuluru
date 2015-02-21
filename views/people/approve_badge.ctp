<?php
if ($success) {
	echo $this->Html->scriptBlock ("jQuery('#$row').remove()");
} else {
	$alert = addslashes(__('Failed to approve the badge.', true));
	echo $this->Html->scriptBlock ("alert('$alert')");
}
?>