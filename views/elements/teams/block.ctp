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
echo $this->ZuluruHtml->link($team['name'],
	array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']),
	$options);
if (array_key_exists ('shirt_colour', $team)) {
	echo ' ' . $this->element('shirt', array('colour' => $team['shirt_colour']));
}

echo $this->element('tooltips');
?>
