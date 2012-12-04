<?php
$this->Html->addCrumb (__('Registrations', true));
$this->Html->addCrumb (__('Unpaid', true));
?>

<div class="registrations index">
<h2><?php __('Unpaid Registrations');?></h2>

<table class="list">
	<tr>
		<th><?php __('Registration');?></th>
		<th><?php __('Player / Event');?></th>
		<th><?php __('Date');?></th>
		<th><?php __('Payment');?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
	$i = 0;
	$total = array('Unpaid' => 0, 'Pending' => 0);
	$order_id_format = Configure::read('registration.order_id_format');
	$affiliate_id = null;
	foreach ($registrations as $registration) {
		if (count($affiliates) > 1 && $registration['Event']['affiliate_id'] != $affiliate_id):
			$affiliate_id = $registration['Event']['affiliate_id'];
	?>
	<tr>
		<th colspan="5">
			<h3 class="affiliate"><?php echo $registration['Event']['Affiliate']['name']; ?></h3>
		</th>
	</tr>
	<?php
		endif;

		$order_id = sprintf($order_id_format, $registration['Registration']['id']);
	?>
	<tr>
		<td><?php echo $this->Html->link($order_id, array('action' => 'view', 'registration' => $registration['Registration']['id'])); ?></td>
		<td><?php echo $this->element('people/block', array('person' => $registration)); ?></td>
		<td><?php echo $this->ZuluruTime->datetime ($registration['Registration']['modified']); ?></td>
		<td><?php echo $registration['Registration']['payment']; ?></td>
		<td class="actions"><?php
		echo $this->Html->link(__('Unregister', true), array('action' => 'unregister', 'registration' => $registration['Registration']['id'], 'return' => true), array(),
					__('Are you sure you want to delete this registration?', true));
		echo $this->Html->link(__('Edit', true), array('action' => 'edit', 'registration' => $registration['Registration']['id'], 'return' => true));
		?></td>
	</tr>
	<tr>
		<td></td>
		<td colspan="4"><?php echo $this->Html->link($registration['Event']['name'], array('controller' => 'events', 'action' => 'view', 'event' => $registration['Event']['id'])); ?></td>
	</tr>
	<?php if (!empty ($registration['Registration']['notes'])): ?>
	<tr>
		<td></td>
		<td colspan="4"><?php echo $registration['Registration']['notes']; ?></td>
	</tr>
	<?php endif; ?>
	<tr><td colspan="5">&nbsp;</td></tr>
	<?php
		$total[$registration['Registration']['payment']] ++;
	}
	?>
</table>

<?php
$total_rows = array();
foreach ($total as $key => $value) {
	$total_rows[] = array ($key, $value);
}

echo $this->Html->tag ('table', $this->Html->tableCells ($total_rows), array('class' => 'list'));
?>

</div>
