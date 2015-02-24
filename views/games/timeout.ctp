<?php
if (isset($error)) {
	echo $this->Html->scriptBlock ("alert('$error')");
} else {
	$taken = sprintf(__('%d taken', true), $taken);
	echo $this->Html->scriptBlock ("jQuery('#score_team_{$this->data['team_id']} span.timeout_count').html('$taken'); jQuery('#TwitterMessage').val('$twitter');");
}
?>
