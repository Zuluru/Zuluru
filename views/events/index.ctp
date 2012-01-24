<?php
$this->Html->addCrumb (__('Registration Events', true));
$this->Html->addCrumb (__('List', true));
?>

<div class="events index">
<h2><?php __('Registration Events List');?></h2>
<?php
echo $this->element('registrations/notice');
if (!$is_logged_in) {
	echo $this->element('events/not_logged_in');
}
?>

<table class="list">
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
			<?php
			echo $this->ZuluruHtml->iconLink('edit_24.png',
				array('action' => 'edit', 'event' => $event['Event']['id']),
				array('alt' => __('Edit', true), 'title' => __('Edit', true)));
			$alt = sprintf(__('Manage %s', true), __('Connections', true));
			echo $this->ZuluruHtml->iconLink('connections_24.png',
				array('action' => 'connections', 'event' => $event['Event']['id']),
				array('alt' => $alt, 'title' => $alt));
			echo $this->ZuluruHtml->iconLink('delete_24.png',
				array('action' => 'delete', 'event' => $event['Event']['id']),
				array('alt' => __('Delete', true), 'title' => __('Delete', true)),
				array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $event['Event']['id'])));
			echo $this->ZuluruHtml->iconLink('summary_24.png',
				array('controller' => 'registrations', 'action' => 'summary', 'event' => $event['Event']['id']),
				array('alt' => __('Summary', true), 'title' => __('Summary', true)));
			$alt = sprintf(__('Add %s', true), __('Preregistration', true));
			echo $this->ZuluruHtml->iconLink('preregistration_add_24.png',
				array('controller' => 'preregistrations', 'action' => 'add', 'event' => $event['Event']['id']),
				array('alt' => $alt, 'title' => $alt));
			?>
		</td>
<?php endif; ?>
	</tr>
<?php endforeach; ?>
</table>
</div>
