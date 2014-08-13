<?php
$this->Html->addCrumb (__(Configure::read('ui.fields_cap'), true));
$this->Html->addCrumb (__('Availability and Bookings', true));
$this->Html->addCrumb ($field['Field']['long_name']);
?>

<div class="fields bookings">
<h2><?php echo __('Availability and Bookings', true) . ': ' . $field['Field']['long_name'];?></h2>

<?php
$is_manager = in_array($field['Facility']['Region']['affiliate_id'], $this->UserCache->read('ManagedAffiliateIDs'));

$seasons = array_unique(Set::extract('/GameSlot/Game/Division/League/long_season', $field));
echo $this->element('selector', array(
		'title' => 'Season',
		'options' => $seasons,
));
$days = array_unique(Set::extract('/GameSlot/Game/Division/Day/name', $field));
echo $this->element('selector', array(
		'title' => 'Day',
		'options' => $days,
));
?>

<table class="list">
	<thead>
		<tr>
			<th><?php __('Date'); ?></th>
			<th><?php __('Start'); ?></th>
			<th><?php __('End'); ?></th>
			<th><?php __('Booking'); ?></th>
			<?php if ($is_admin || $is_manager): ?>
			<th><?php __('Actions'); ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tbody>
<?php
foreach ($field['GameSlot'] as $slot):
	$seasons = array_unique(Set::extract('/Game/Division/League/long_season', $slot));
	$day = date('l', strtotime($slot['game_date']));

	$divisions = array();
	foreach ($slot['Game'] as $game) {
		if (!array_key_exists($game['Division']['id'], $divisions)) {
			$divisions[$game['Division']['id']] = array('division' => $game['Division'], 'games' => array());
		}
		$divisions[$game['Division']['id']]['games'][] = $this->element('games/block', array('game' => array('Game' => $game, 'GameSlot' => $slot), 'field' => 'id'));
	}
	$rows = max(count($divisions), 1);
?>
		<tr class="<?php echo $this->element('selector_classes', array('title' => 'Season', 'options' => $seasons)); ?> <?php echo $this->element('selector_classes', array('title' => 'Day', 'options' => $day)); ?>">
			<td rowspan="<?php echo $rows; ?>"><?php echo $this->ZuluruTime->date ($slot['game_date']); ?></td>
			<?php if ($is_admin || $is_manager): ?>
			<td rowspan="<?php echo $rows; ?>"><?php echo $this->Html->link ($this->ZuluruTime->time ($slot['game_start']),
						array('controller' => 'game_slots', 'action' => 'view', 'slot' => $slot['id'])); ?></td>
			<?php else: ?>
			<td rowspan="<?php echo $rows; ?>"><?php echo $this->ZuluruTime->time ($slot['game_start']); ?></td>
			<?php endif; ?>
			<td rowspan="<?php echo $rows; ?>"><?php echo $this->ZuluruTime->time ($slot['display_game_end']); ?></td>
			<td><?php
			if (empty($divisions)) {
				echo '---- ' . __('open', true) . ' ----';
			} else {
				$division = array_shift($divisions);
				echo $this->element('divisions/block', array('division' => $division['division'], 'field' => 'league_name')) .
						' (' . __n('game', 'games', count($division['games']), true) . ' ' . implode(', ', $division['games']) . ')';
			}
			?></td>
			<?php if ($is_admin || $is_manager): ?>
			<td rowspan="<?php echo $rows; ?>" class="actions"><?php
				echo $this->Html->link (__('Edit', true),
						array('controller' => 'game_slots', 'action' => 'edit', 'slot' => $slot['id'], 'return' => true));
				echo $this->Html->link (__('Delete', true),
						array('controller' => 'game_slots', 'action' => 'delete', 'slot' => $slot['id'], 'return' => true));
				// TODO: This doesn't work in the old Leaguerunner, rescheduling is only for leagues, not games
				/*
				if (!empty ($slot['Game'])) {
					echo $this->Html->link (__('Reschedule/Move', true),
							array('controller' => 'games', 'action' => 'reschedule', 'game' => $slot['Game']['id']));
				}
				*/
			?></td>
			<?php endif; ?>
		</tr>
<?php foreach ($divisions as $division): ?>
		<tr>
			<td><?php echo $this->element('divisions/block', array('division' => $division['division'], 'field' => 'league_name')) .
						' (' . __n('game', 'games', count($division['games']), true) . ' ' . implode(', ', $division['games']) . ')';
			?></td>
		</tr>
<?php endforeach; ?>
<?php endforeach; ?>
	</tbody>
</table>
</div>
