<h2><?php echo $team['Team']['name']; ?></h2>
<dl>
<?php if (Configure::read('feature.shirt_colour') && !empty($team['Team']['shirt_colour'])): ?>
	<dt><?php __('Shirt colour'); ?></dt>
	<dd><?php echo $team['Team']['shirt_colour']; ?></dd>
<?php endif; ?>

<?php if ($is_logged_in && !empty ($team['Person'])):
	$links = array();
	foreach (Configure::read('privileged_roster_roles') as $role) {
		$captains = Set::extract ("/Person/TeamsPerson[role=$role]/..", $team);
		foreach ($captains as $captain) {
			$link = $this->Html->link($captain['Person']['full_name'], array('controller' => 'people', 'action' => 'view', 'person' => $captain['Person']['id']));
			if ($role == 'assistant') {
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
	<dd><?php echo $this->Html->link(__('Details & roster', true), array('controller' => 'teams', 'action' => 'view', 'team' => $team['Team']['id'])) .
			' / ' .
			$this->Html->link(__('Schedule', true), array('controller' => 'teams', 'action' => 'schedule', 'team' => $team['Team']['id'])) .
			' / ' .
			$this->Html->link(__('Standings', true), array('controller' => 'divisions', 'action' => 'standings', 'division' => $team['Team']['division_id'], 'team' => $team['Team']['id']));
	if ($is_logged_in && Configure::read('scoring.stat_tracking') && League::hasStats($team['Division']['League'])) {
		echo ' / ' . $this->Html->link(__('Stats', true), array('controller' => 'teams', 'action' => 'stats', 'team' => $team['Team']['id']));
	}
	if (Configure::read('feature.urls') && !empty ($team['Team']['website'])) {
		echo ' / ' . $this->Html->link(__('Website', true), $team['Team']['website']);
	}
?>
	</dd>

	<dt><?php __('Division'); ?></dt>
	<dd><?php
	$title = array('title' => $team['Division']['full_league_name']);
	echo $this->Html->link(__('Details', true), array('controller' => 'divisions', 'action' => 'view', 'division' => $team['Team']['division_id']), $title) .
		' / ' .
		$this->Html->link(__('Schedule', true), array('controller' => 'divisions', 'action' => 'schedule', 'division' => $team['Team']['division_id'])) .
		' / ' .
		$this->Html->link(__('Standings', true), array('controller' => 'divisions', 'action' => 'standings', 'division' => $team['Team']['division_id']));
	?></dd>

	<?php if ($is_logged_in && Configure::read('feature.annotations')): ?>
	<dt><?php __('Notes'); ?></dt>
	<dd><?php
	if (!empty($team['Note'])) {
		echo $this->Html->link(__('Delete', true), array('action' => 'delete_note', 'team' => $team['Team']['id'])) . ' / ';
		$link = 'Edit';
	} else {
		$link = 'Add';
	}
	echo $this->Html->link(__($link, true), array('action' => 'note', 'team' => $team['Team']['id']));
	?></dd>
	<?php endif; ?>

</dl>
