<?php
echo $this->Form->input('team_id', array(
		'label' => __('Pulling team', true),
		'options' => array(
			$team['id'] => $team['name'],
			$opponent['id'] => $opponent['name'],
		),
));
?>
