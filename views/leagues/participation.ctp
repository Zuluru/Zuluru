<?php
$this->Html->addCrumb (__('Leagues', true));
$this->Html->addCrumb ($league['League']['full_name']);
$this->Html->addCrumb (__('Participation', true));
?>

<div class="leagues index">
<h2><?php echo __('Participation', true) . ': ' . $league['League']['full_name'];?></h2>

<table class="list">
<tr>
	<th><?php __('Team');?></th>
	<th><?php __('Person'); ?></th>
	<th><?php __('Role'); ?></th>
	<th><?php __('Date');?></th>
</tr>
<?php foreach ($league['Division'] as $division): ?>
<tr>
	<td colspan="4"><h3><?php echo $division['name']; ?></h3></td>
</tr>
<?php
	foreach ($division['Team'] as $team):
		$team_name = $this->element('teams/block', compact('team'));
		usort ($team['Person'], array('Team', 'compareRoster'));
		foreach ($team['Person'] as $person):
?>
	<tr>
		<td><?php echo $team_name; ?></td>
		<td><?php echo $this->element('people/block', compact('person')); ?></td>
		<td><?php echo $this->element('people/roster_role', array('roster' => $person['TeamsPerson'], 'division' => $division)); ?></td>
		<td><?php echo $this->ZuluruTime->date($person['TeamsPerson']['created']); ?></td>
	</tr>
<?php
			$team_name = null;
		endforeach;
	endforeach;
endforeach;
?>
</table>
</div>

<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(sprintf(__('View %s', true), __('League', true)), array('controller' => 'leagues', 'action' => 'view', 'league' => $league['League']['id'])); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('Download %s List', true), __('Participation', true)), array('action' => 'participation', 'league' => $league['League']['id'], 'ext' => 'csv')); ?> </li>
	</ul>
</div>

<?php echo $this->element('people/roster_div'); ?>
