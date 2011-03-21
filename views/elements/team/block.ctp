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
<h2><?php echo $team['name']; ?></h2>
<dl>
	<dt><?php __('Shirt colour'); ?></dt>
	<dd><?php echo $team['shirt_colour']; ?></dd>

<?php if ($is_logged_in && !empty ($team['Person'])):
	$links = array();
	foreach (Configure::read('privileged_roster_positions') as $position) {
		$captains = Set::extract ("/Person/TeamsPerson[status=$position]/..", $team);
		foreach ($captains as $captain) {
			$link = $this->Html->link($captain['Person']['full_name'], array('controller' => 'people', 'action' => 'view', 'person' => $captain['Person']['id']));
			if ($position == 'assistant') {
				$link .= ' (A)';
			}
			$links[] = $link;
		}
	}
?>
	<dt><?php __('Captains'); ?></dt>
	<dd><?php echo implode(', ', $links); ?></dd>
<?php endif; ?>

	<dt><?php __('Team'); ?></dt>
	<dd><?php echo $this->Html->link(__('Details & roster', true), array('controller' => 'teams', 'action' => 'view', 'team' => $team['id'])); ?>

<?php
if ($team['league_id']) {
	echo ' / ' .
		$this->Html->link(__('Schedule', true), array('controller' => 'teams', 'action' => 'schedule', 'team' => $team['id'])) .
		' / ' .
		$this->Html->link(__('Standings', true), array('controller' => 'leagues', 'action' => 'standings', 'league' => $team['league_id'], 'team' => $team['id']));
}
if (!empty ($team['website'])) {
	echo ' / ' . $this->Html->link(__('Website', true), $team['website']);
}
?>
	</dd>

<?php if ($team['league_id']): ?>
	<dt><?php __('League'); ?></dt>
	<dd><?php
	echo $this->Html->link(__('Details', true), array('controller' => 'leagues', 'action' => 'view', 'league' => $team['league_id'])) .
		' / ' .
		$this->Html->link(__('Schedule', true), array('controller' => 'leagues', 'action' => 'schedule', 'league' => $team['league_id'])) .
		' / ' .
		$this->Html->link(__('Standings', true), array('controller' => 'leagues', 'action' => 'standings', 'league' => $team['league_id']));
	?></dd>
<?php endif; ?>

</dl>
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
