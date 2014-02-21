<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb ($person['Person']['full_name']);
$this->Html->addCrumb (__('Credits', true));
?>

<div class="players index">
<h2><?php __('Unused Credits');?></h2>
<p>You can use these credits to pay for things in the checkout page.</p>

<table class="list">
	<tr>
		<th><?php __('Date'); ?></th>
		<th><?php __('Initial Amount'); ?></th>
		<th><?php __('Amount Used'); ?></th>
		<th><?php __('Notes'); ?></th>
	</tr>
	<?php
	$i = 0;
	$affiliate_id = null;
	foreach ($person['Credit'] as $credit):
		if (count($affiliates) > 1 && $credit['affiliate_id'] != $affiliate_id):
			$affiliate_id = $credit['affiliate_id'];
	?>
	<tr>
		<th colspan="4">
			<h3 class="affiliate"><?php echo $credit['Affiliate']['name']; ?></h3>
		</th>
	</tr>
	<?php
		endif;
	?>
	<tr>
		<td><?php echo $this->ZuluruTime->date($credit['created']);?></td>
		<td><?php echo $credit['amount'];?></td>
		<td><?php echo $credit['amount_used'];?></td>
		<td><?php echo $credit['notes'];?></td>
	</tr>
	<?php endforeach; ?>
</table>

</div>
