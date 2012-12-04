<?php
$this->Html->addCrumb (__('Registrations', true));
$this->Html->addCrumb (__('Waiting List', true));
$this->Html->addCrumb ($event['Event']['name']);
?>

<div class="registrations index">
<h2><?php echo __('Waiting List', true) . ': ' . $event['Event']['name']; ?></h2>

<table class="list">
	<tr>
		<th><?php __('Registration');?></th>
		<th><?php __('Player');?></th>
		<th><?php __('Date');?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
	$i = 0;
	$order_id_format = Configure::read('registration.order_id_format');
	foreach ($registrations as $registration):
		$order_id = sprintf($order_id_format, $registration['Registration']['id']);
	?>
	<tr>
		<td><?php echo $this->Html->link($order_id, array('action' => 'view', 'registration' => $registration['Registration']['id'])); ?></td>
		<td><?php echo $this->element('people/block', array('person' => $registration)); ?></td>
		<td><?php echo $this->ZuluruTime->datetime ($registration['Registration']['created']); ?></td>
		<td class="actions"><?php
		echo $this->Html->link(__('Unregister', true), array('action' => 'unregister', 'registration' => $registration['Registration']['id'], 'return' => true), array(),
					__('Are you sure you want to delete this registration?', true));
		echo $this->Html->link(__('Edit', true), array('action' => 'edit', 'registration' => $registration['Registration']['id'], 'return' => true));
		?></td>
	</tr>
	<?php if (!empty ($registration['Registration']['notes'])): ?>
	<tr>
		<td></td>
		<td colspan="4"><?php echo $registration['Registration']['notes']; ?></td>
	</tr>
	<?php endif; ?>
	<?php endforeach; ?>
</table>

</div>
