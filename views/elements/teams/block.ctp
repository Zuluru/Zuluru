<?php
// Sometimes, there will be a 'Team' key, sometimes not
if (array_key_exists ('Team', $team)) {
	$team = array_merge ($team, $team['Team']);
	unset ($team['Team']);
}
$id = "team{$team['id']}";
if (array_key_exists ('division_id', $team)) {
	$division_id = $team['division_id'];
} else if (array_key_exists ('Division', $team) && array_key_exists ('id', $team['Division'])) {
	$division_id = $team['Division']['id'];
} else {
	$division_id = null;
}

if (isset ($options)) {
	$options = array_merge (array('class' => $id), $options);
} else {
	$options = array('class' => $id);
}
echo $this->ZuluruHtml->link($team['name'],
	array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']),
	$options);
if (array_key_exists ('shirt_colour', $team)) {
	echo ' ' . $this->element('shirt', array('colour' => $team['shirt_colour']));
}

// Global variable. Ew.
global $team_blocks_shown;
if (!isset($team_blocks_shown)) {
	$team_blocks_shown = array();
}
if (!in_array($team['id'], $team_blocks_shown)) {
	$team_blocks_shown[] = $team['id'];
	$this->ZuluruHtml->buffer($this->element('teams/tooltip', compact('team', 'id', 'division_id')));
	$this->Js->buffer("
$('.$id').tooltip({
	cancelDefault: false,
	delay: 1,
	predelay: 500,
	relative: true,
	tip: '#$id'
});
");
}
?>
