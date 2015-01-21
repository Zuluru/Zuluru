<?php
echo $this->Form->input('team_id', array(
		'label' => __('Team taking kick-off', true),
		'options' => array(
			$team['id'] => $team['name'],
			$opponent['id'] => $opponent['name'],
		),
));
?>
