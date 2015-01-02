<?php
$this->Html->addCrumb (__('People', true));
$this->Html->addCrumb ($person['Person']['full_name']);
$this->Html->addCrumb (__('Waiver History', true));
?>

<div class="waivers index">
<h2><?php echo __('Waiver History', true) . ': ' . $person['Person']['full_name'];?></h2>
<?php if (empty($person['Waiver'])): ?>
<p><?php $person['Person']['id'] == $this->UserCache->read('Person.id') ? __('You have never signed a waiver.') : __('This person has never signed a waiver.'); ?></p>
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

<?php if (!empty($waivers)): ?>
<h3>You have not signed the following waivers for a period covering today's date.</h3>
<table class="list">
	<?php
	$i = 0;
	$affiliate_id = null;
	foreach ($waivers as $waiver):
		if (count($affiliates) > 1 && $waiver['Waiver']['affiliate_id'] != $affiliate_id):
			$affiliate_id = $waiver['Waiver']['affiliate_id'];
	?>
	<tr>
		<th colspan="2">
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
		<td><?php
		list ($valid_from, $valid_until) = Waiver::_validRange(date('Y-m-d'), $waiver['Waiver']);
		echo $waiver['Waiver']['name'] . ' ' . __('covering', true) . ' ' .
				$this->ZuluruTime->date($valid_from) . ' ' . __('to', true) . ' ' .
				$this->ZuluruTime->date($valid_until);
		?></td>
		<td class="actions">
		<?php
		echo $this->ZuluruHtml->link(__('Sign', true), array('controller' => 'waivers', 'action' => 'sign', 'waiver' => $waiver['Waiver']['id'], 'date' => date('Y-m-d')));
		?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

</div>
