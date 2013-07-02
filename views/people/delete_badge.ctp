<?php
if ($success) {
	echo $this->Html->scriptBlock ("
		// Remove the reason
		jQuery('span.id_$id').remove();
		// Remove the comma that was before or after it
		var r = jQuery('#$row td.reasons').html();
		if (r.substr(0,2) == ', ') {
			r = r.substr(2);
		} else if (r.substr(r.length - 2, 2) == ', ') {
			r = r.substr(0, r.length - 2);
		} else {
			r = r.replace(', , ', ', ');
		}
		// If there's nothing left, remove the entire row
		if (r == '') {
			jQuery('#$row').remove();
		} else {
			jQuery('#$row td.reasons').html(r);
		}
	");
} else {
	echo $this->Html->scriptBlock ("alert('Failed to delete the badge.')");
}
?>