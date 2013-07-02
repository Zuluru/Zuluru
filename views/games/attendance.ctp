<?php
$this->Html->addCrumb (__('Games', true));
$this->Html->addCrumb (__('Attendance', true));
$this->Html->addCrumb ($team['name']);
$this->Html->addCrumb ($this->ZuluruTime->date($game['GameSlot']['game_date']));
?>

<div class="games">
<h2><?php  __('Attendance'); ?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('League', true) . '/' . __('Division', true); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->element('divisions/block', array('division' => $game['Division'], 'field' => 'full_league_name')); ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Game Date'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>><?php echo $this->Html->link($this->ZuluruTime->date($game['GameSlot']['game_date']),
			array('action' => 'view', 'game' => $game['Game']['id'])); ?></dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Game Time'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>><?php echo $this->ZuluruTime->time($game['GameSlot']['game_start']); ?> - <?php echo $this->ZuluruTime->time($game['GameSlot']['display_game_end']); ?></dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Team'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>><?php echo $this->element('teams/block', array('team' => $team)); ?></dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Opponent'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>><?php echo $this->element('teams/block', array('team' => $opponent)); ?></dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Location');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->element('fields/block', array('field' => $game['GameSlot']['Field'], 'display_field' => 'long_name')); ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Totals'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>><?php
		// Build the totals
		$statuses = Configure::read('attendance');
		$alt = Configure::read('attendance_alt');
		$count = array_fill_keys(array_keys($statuses), array('Male' => 0, 'Female' => 0));
		foreach ($attendance['Person'] as $person) {
			if (!array_key_exists (0, $person['Attendance']))
				continue;
			$record = $person['Attendance'][0];
			$status = $record['status'];
			++$count[$status][$person['gender']];
		}

		foreach ($statuses as $status => $description) {
			$counts = array();
			foreach (array('Male', 'Female') as $gender) {
				if ($count[$status][$gender]) {
					$counts[] = $count[$status][$gender] . substr (__($gender, true), 0, 1);
				}
			}
			if (!empty ($counts)) {
				$low = Inflector::slug(low($statuses[$status]));
				$short = $this->ZuluruHtml->icon("attendance_{$low}_dedicated_24.png", array(
						'title' => sprintf (__('Attendance: %s', true), __($statuses[$status], true)),
						'alt' => $alt[$status],
				));
				echo $short . ': ' . implode(' / ', $counts) . '&nbsp;';
			}
		}
		?></dd>
	</dl>

<?php
$can_annotate = Configure::read('feature.annotations') && in_array($team['id'], $this->Session->read('Zuluru.TeamIDs'));
?>
<div class="actions">
	<ul>
		<?php if ($can_annotate): ?>
		<li><?php echo $this->Html->link(__('Add Note', true), array('action' => 'note', 'game' => $game['Game']['id'])); ?></li>
		<?php endif; ?>
		<?php if ($is_captain && Configure::read('scoring.stat_tracking') && League::hasStats($game['Division']['League'])): ?>
		<li><?php echo $this->ZuluruHtml->iconLink('pdf_32.png',
					array('controller' => 'games', 'action' => 'stat_sheet', 'team' => $team['id'], 'game' => $game['Game']['id']),
					array('alt' => __('Stat Sheet', true), 'title' => __('Stat Sheet', true)),
					array('confirm' => __('This stat sheet will only include players who have indicated that they are playing, plus a couple of blank lines.\n\nFor a stat sheet with your full roster, use the link from the team view page.', true))); ?> </li>
		<?php endif; ?>
	</ul>
</div>

<div class="related">
	<table class="list">
	<thead>
	<tr>
		<th><?php __('Name'); ?></th>
		<th><?php __('Role'); ?></th>
		<th><?php __('Gender'); ?></th>
		<th><?php __('Rating'); ?></th>
		<th><?php __('Attendance'); ?></th>
		<th><?php __('Updated'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	$i = 1;
	foreach ($attendance['Person'] as $person):
		if (!array_key_exists (0, $person['Attendance']))
			continue;
		$record = $person['Attendance'][0];
		$status = $record['status'];
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $this->element('people/block', compact('person')); ?></td>
		<td><?php __(Configure::read("options.roster_role.{$person['TeamsPerson']['role']}")); ?></td>
		<td><?php __($person['gender']);?></td>
		<td><?php echo $person['skill_level'];?></td>
		<td class="<?php echo low($statuses[$status]);?>"><?php
			echo $this->element('games/attendance_change', array(
				'team' => $team,
				'game_id' => $game['Game']['id'],
				'game_date' => $game['GameSlot']['game_date'],
				'game_time' => $game['GameSlot']['game_start'],
				'person_id' => $person['id'],
				'role' => $person['TeamsPerson']['role'],
				'status' => $status,
				'comment' => $record['comment'],
				'dedicated' => true,
			));
		?></td>
		<td><?php
		if ($record['created'] != $record['updated']) {
			echo $this->ZuluruTime->datetime($record['updated']);
		}
		?></td>
	</tr>
	<?php endforeach; ?>

	</tbody>
	</table>
</div>

<?php echo $this->element('games/attendance_div'); ?>
