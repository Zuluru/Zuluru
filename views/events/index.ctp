<?php
$this->Html->addCrumb (__('Registration Events', true));
$this->Html->addCrumb (__('List', true));
?>

<div class="events index">
<h2><?php __('Registration Events List');?></h2>
<?php echo $this->element('registration/notice'); ?>

<table>
<tr>
	<th><?php __('Registration'); ?></th>
	<th><?php __('Cost'); ?></th>
	<th><?php __('Opens on'); ?></th>
	<th><?php __('Closes on'); ?></th>
<?php if ($is_admin): ?>
	<th><?php __('Actions'); ?></th>
<?php endif; ?>
</tr>
<?php
$i = 0;
$last_name = null;
foreach ($events as $event):
	if ($event['EventType']['name'] != $last_name) {
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
		echo "<tr$class><td colspan='" . ($is_admin ? 5 : 4) . "'><h3>{$event['EventType']['name']}</h3></td></tr>";
		$last_name = $event['EventType']['name'];
	}
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $this->Html->link(__($event['Event']['name'], true), array('action' => 'view', 'event' => $event['Event']['id'])); ?>
		</td>
		<td>
			<?php
			$cost = $event['Event']['cost'] + $event['Event']['tax1'] + $event['Event']['tax2'];
			if ($cost > 0) {
				echo '$' . $cost;
			} else {
				echo $this->Html->tag ('span', 'FREE', array('class' => 'free'));
			}
			?>
		</td>
		<td>
			<?php echo $this->ZuluruTime->datetime($event['Event']['open']); ?>
		</td>
		<td>
			<?php echo $this->ZuluruTime->datetime($event['Event']['close']); ?>
		</td>
<?php if ($is_admin): ?>
		<td class="actions">
			<?php echo $this->Html->link(__('Edit', true), array('action' => 'edit', 'event' => $event['Event']['id'])); ?>
			<?php echo $this->Html->link(__('Delete', true), array('action' => 'delete', 'event' => $event['Event']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $event['Event']['id'])); ?>
			<?php echo $this->Html->link(__('Summary', true), array('controller' => 'registrations', 'action' => 'summary', 'event' => $event['Event']['id'])); ?>
		</td>
<?php endif; ?>
	</tr>
<?php endforeach; ?>
</table>
</div>
