<?php
$this->Html->addCrumb (__('Registration', true));
$this->Html->addCrumb (__('Redeem Credit', true));
?>

<div class="registrations form">
<h2><?php __('Redeem Credit');?></h2>
<?php
$balance = $registration['Registration']['total_amount'] - array_sum(Set::extract('/Payment/payment_amount', $registration));

echo $this->Html->para(null, sprintf(__('You have requested to redeem a credit towards payment of your registration for %s. You have an outstanding balance of $%.02f on this registration.', true),
		$this->Html->link($registration['Event']['name'], array('controller' => 'events', 'action' => 'view', 'event' => $registration['Event']['id'])), $balance));

if (count($registration['Person']['Credit']) == 1) {
	$credit = $registration['Person']['Credit'][0]['amount'] - $registration['Person']['Credit'][0]['amount_used'];
	if ($credit > $balance) {
		echo $this->Html->para(null, sprintf(__('If you apply your credit, the balance will be covered, and you will still have a credit of $%.02f remaining.', true), $credit - $balance));
	} else if ($credit == $balance) {
		echo $this->Html->para(null, __('If you apply your credit, the balance will be covered, and your credit will be used up.', true));
	} else {
		echo $this->Html->para(null, sprintf(__('If you apply your credit, it will be used up, and you will still have a balance of $%.02f owing on the registration.', true), $balance - $credit));
	}
	echo $this->Html->para(null, $this->Html->link(__('Apply the credit now', true),
			array('action' => 'redeem', 'registration' => $registration['Registration']['id'], 'credit' => $registration['Person']['Credit'][0]['id']),
			null,
			__('Are you sure you want to apply this credit? This cannot be undone.', true)
	));
} else {
	echo $this->Html->para(null, __('You have the following credits to redeem:', true));
?>
<table class="list">
	<tr>
		<th><?php __('Date'); ?></th>
		<th><?php __('Initial Amount'); ?></th>
		<th><?php __('Amount Used'); ?></th>
		<th><?php __('Actions'); ?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($registration['Person']['Credit'] as $credit):
	?>
	<tr>
		<td><?php echo $this->ZuluruTime->date($credit['created']);?></td>
		<td><?php echo $credit['amount'];?></td>
		<td><?php echo $credit['amount_used'];?></td>
		<td class="actions"><?php
		echo $this->Html->link(__('Apply credit', true),
			array('action' => 'redeem', 'registration' => $registration['Registration']['id'], 'credit' => $credit['id']),
			null,
			__('Are you sure you want to apply this credit? This cannot be undone.', true)
		);
		?></td>
	</tr>
	<?php endforeach; ?>
</table>
<?php
}
?>