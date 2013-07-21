<?php
if (isset($error)) {
	echo $this->Html->scriptBlock ("alert('$error')");
} else {
	echo $this->Html->scriptBlock ("jQuery('#score_team_{$this->data['team_id']} td.score').html('$team_score'); jQuery('#TwitterMessage').val('$twitter');");
}
?>
