<?php
// Sometimes, there will be a 'Team' key, sometimes not
if (array_key_exists ('Team', $team)) {
	$team = array_merge ($team, $team['Team']);
	unset ($team['Team']);
}
$id = "teams_team_{$team['id']}";

if (isset ($options)) {
	$options = array_merge (array('id' => $id, 'class' => 'trigger'), $options);
} else {
	$options = array('id' => $id, 'class' => 'trigger');
}
if (isset ($max_length)) {
	$options['max_length'] = $max_length;
}

echo $this->ZuluruHtml->link($team['name'],
	array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']),
	$options);
if (Configure::read('feature.shirt_colour') && array_key_exists ('shirt_colour', $team) && (!isset($show_shirt) || $show_shirt)) {
	echo ' ' . $this->element('shirt', array('colour' => $team['shirt_colour']));
}

echo $this->element('tooltips');
?>
