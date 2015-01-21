<?php
echo $this->Form->input('team_id', array(
		'label' => __('Batting team', true),
		'options' => array(
			$team['id'] => $team['name'],
			$opponent['id'] => $opponent['name'],
		),
));
?>
