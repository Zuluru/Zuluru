<div id="<?php echo $id; ?>" class="tooltip">
<h2><?php echo $team['name']; ?></h2>
<dl>
<?php if (array_key_exists ('shirt_colour', $team)): ?>
	<dt><?php __('Shirt colour'); ?></dt>
	<dd><?php echo $team['shirt_colour']; ?></dd>
<?php endif; ?>

<?php if ($is_logged_in && !empty ($team['Person'])):
	$links = array();
	foreach (Configure::read('privileged_roster_positions') as $position) {
		$captains = Set::extract ("/Person/TeamsPerson[position=$position]/..", $team);
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
if ($division_id) {
	echo ' / ' .
		$this->Html->link(__('Schedule', true), array('controller' => 'teams', 'action' => 'schedule', 'team' => $team['id'])) .
		' / ' .
		$this->Html->link(__('Standings', true), array('controller' => 'divisions', 'action' => 'standings', 'division' => $division_id, 'team' => $team['id']));
}
if (!empty ($team['website'])) {
	echo ' / ' . $this->Html->link(__('Website', true), $team['website']);
}
?>
	</dd>

<?php if ($division_id): ?>
	<dt><?php __('Division'); ?></dt>
	<dd><?php
	if (array_key_exists ('Division', $team)) {
		$title = array('title' => $team['Division']['full_league_name']);
	} else {
		$title = array();
	}
	echo $this->Html->link(__('Details', true), array('controller' => 'divisions', 'action' => 'view', 'division' => $division_id), $title) .
		' / ' .
		$this->Html->link(__('Schedule', true), array('controller' => 'divisions', 'action' => 'schedule', 'division' => $division_id)) .
		' / ' .
		$this->Html->link(__('Standings', true), array('controller' => 'divisions', 'action' => 'standings', 'division' => $division_id));
	?></dd>
<?php endif; ?>

</dl>
</div>