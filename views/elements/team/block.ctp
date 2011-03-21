<?php
$id = "team{$team['id']}";

// Global variable. Ew.
global $team_blocks_shown;
if (!isset($team_blocks_shown)) {
	$team_blocks_shown = array();
}
if (!in_array($team['id'], $team_blocks_shown)) {
	$team_blocks_shown[] = $team['id'];
?>
<div id="<?php echo $id; ?>" class="tooltip">
<?php echo $team['name']; ?>
<br /><?php echo __('Shirt colour', true) . ': ' . $team['shirt_colour']; ?>
<br /><?php
echo __('Team', true) . ': ' .
	$this->Html->link(__('Details and roster', true), array('controller' => 'teams', 'action' => 'view', 'team' => $team['id'])) .
	' / ' .
	$this->Html->link(__('Schedule', true), array('controller' => 'teams', 'action' => 'schedule', 'team' => $team['id'])) .
	' / ' .
	$this->Html->link(__('Standings', true), array('controller' => 'leagues', 'action' => 'standings', 'league' => $team['league_id'], 'team' => $team['id']));
?>
<br /><?php
echo __('League', true) . ': ' .
	$this->Html->link(__('Details', true), array('controller' => 'leagues', 'action' => 'view', 'league' => $team['league_id'])) .
	' / ' .
	$this->Html->link(__('Schedule', true), array('controller' => 'leagues', 'action' => 'schedule', 'league' => $team['league_id']));
?>
</div>
<?php
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

if (isset ($options)) {
	$options = array_merge (array('class' => $id), $options);
} else {
	$options = array('class' => $id);
}
echo $this->ZuluruHtml->link($team['name'],
	array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']),
	$options) .
	' ' . $this->element('shirt', array('colour' => $team['shirt_colour']));
?>
