<?php
if (isset($error)) {
	echo $this->Html->scriptBlock ("alert('$error')");
} else {
	echo $this->Html->scriptBlock ("jQuery('#score_team_{$this->data['team_id']} span.timeout_count').html('$taken'); jQuery('#TwitterMessage').val('$twitter');");
}
?>
