<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb ($person['Person']['full_name']);
$this->Html->addCrumb (__('Waiver History', true));
?>

<div class="waivers index">
<h2><?php echo __('Waiver History', true) . ': ' . $person['Person']['full_name'];?></h2>
<?php if (empty($person['Waiver'])): ?>
<p>This person has never signed a waiver.</p>
<?php else: ?>

<table class="list">
	<tr>
		<th><?php __('Waiver');?></th>
		<th><?php __('Signed');?></th>
		<th><?php __('Valid From');?></th>
		<th><?php __('Valid Until');?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
	$i = 0;
	$affiliate_id = null;
	foreach ($person['Waiver'] as $waiver):
		if (count($affiliates) > 1 && $waiver['affiliate_id'] != $affiliate_id):
			$affiliate_id = $waiver['affiliate_id'];
	?>
	<tr>
		<th colspan="5">
			<h3 class="affiliate"><?php echo $waiver['Affiliate']['name']; ?></h3>
		</th>
	</tr>
	<?php
		endif;

		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $waiver['name']; ?></td>
		<td><?php echo $this->ZuluruTime->fulldate($waiver['WaiversPerson']['created']); ?></td>
		<td><?php echo $this->ZuluruTime->fulldate($waiver['WaiversPerson']['valid_from']); ?></td>
		<td><?php echo $this->ZuluruTime->fulldate($waiver['WaiversPerson']['valid_until']); ?></td>
		<td class="actions"><?php echo $this->ZuluruHtml->iconLink('view_24.png', array('controller' => 'waivers', 'action' => 'review', 'waiver' => $waiver['id'], 'date' => $waiver['WaiversPerson']['valid_from'])); ?></td>
	</tr>
	<?php endforeach; ?>
</table>

<?php endif; ?>

</div>
