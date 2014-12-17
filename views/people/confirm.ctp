<?php
if ($success) {
	$message = __('Profile details have been confirmed, thank you.\\nYou will be reminded about this again periodically.', true);
} else {
	$message = __('Failed to update profile details.\\nYou will likely be prompted about this again very soon.\\n\\nIf problems persist, contact your system administrator.', true);
}
echo $this->Html->scriptBlock("alert('$message');");
?>