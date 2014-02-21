<?php
$this->Html->addCrumb (__('Registrations', true));
$this->Html->addCrumb (__('Credits', true));
?>

<div class="registrations index">
<h2><?php __('Unused Credits');?></h2>

<table class="list">
	<tr>
		<th><?php __('Player');?></th>
		<th><?php __('Date'); ?></th>
		<th><?php __('Initial Amount'); ?></th>
		<th><?php __('Amount Used'); ?></th>
		<th><?php __('Notes'); ?></th>
	</tr>
	<?php
	$i = 0;
	$affiliate_id = null;
	foreach ($credits as $credit):
		if (count($affiliates) > 1 && $credit['Credit']['affiliate_id'] != $affiliate_id):
			$affiliate_id = $credit['Credit']['affiliate_id'];
	?>
	<tr>
		<th colspan="5">
			<h3 class="affiliate"><?php echo $credit['Credit']['Affiliate']['name']; ?></h3>
		</th>
	</tr>
	<?php
		endif;
	?>
	<tr>
		<td><?php echo $this->element('people/block', array('person' => $credit)); ?></td>
		<td><?php echo $this->ZuluruTime->date($credit['Credit']['created']);?></td>
		<td><?php echo $credit['Credit']['amount'];?></td>
		<td><?php echo $credit['Credit']['amount_used'];?></td>
		<td><?php echo $credit['Credit']['notes'];?></td>
	</tr>
	<?php endforeach; ?>
</table>

</div>
