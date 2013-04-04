<?php
$this->Html->addCrumb (__(Configure::read('ui.fields_cap'), true));
$this->Html->addCrumb (__('Availability and Bookings', true));
$this->Html->addCrumb ($field['Field']['long_name']);
?>

<div class="fields bookings">
<h2><?php echo __('Availability and Bookings', true) . ': ' . $field['Field']['long_name'];?></h2>

<?php
$is_manager = in_array($field['Facility']['Region']['affiliate_id'], $this->Session->read('Zuluru.ManagedAffiliateIDs'));
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
<?php foreach ($field['GameSlot'] as $slot): ?>
		<tr>
			<td><?php echo $this->ZuluruTime->date ($slot['game_date']); ?></td>
			<?php if ($is_admin || $is_manager): ?>
			<td><?php echo $this->Html->link ($this->ZuluruTime->time ($slot['game_start']),
						array('controller' => 'game_slots', 'action' => 'view', 'slot' => $slot['id'])); ?></td>
			<?php else: ?>
			<td><?php echo $this->ZuluruTime->time ($slot['game_start']); ?></td>
			<?php endif; ?>
			<td><?php echo $this->ZuluruTime->time ($slot['display_game_end']); ?></td>
			<td><?php
			if (!empty ($slot['Game'])) {
				echo $this->element('divisions/block', array('division' => $slot['Game']['Division'], 'field' => 'league_name',
						'url' => array('controller' => 'games', 'action' => 'view', 'game' => $slot['Game']['id'])));
			}
			?></td>
			<?php if ($is_admin || $is_manager): ?>
			<td class="actions"><?php
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
<?php endforeach; ?>
	</tbody>
</table>
</div>
