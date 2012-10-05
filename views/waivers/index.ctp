<?php
$this->Html->addCrumb (__('Waivers', true));
$this->Html->addCrumb (__('List', true));
?>

<div class="waivers index">
<h2><?php __('Waivers');?></h2>
<table class="list">
	<tr>
		<th><?php __('Id'); ?></th>
		<th><?php __('Name'); ?></th>
		<th><?php __('Description'); ?></th>
		<th><?php __('Active'); ?></th>
		<th><?php __('Expiry Type'); ?></th>
		<th><?php __('Valid For'); ?></th>
		<th class="actions"><?php __('Actions'); ?></th>
	</tr>
	<?php
	$i = 0;
	$affiliate_id = null;
	foreach ($waivers as $waiver):
		if (count($affiliates) > 1 && $waiver['Waiver']['affiliate_id'] != $affiliate_id):
			$affiliate_id = $waiver['Waiver']['affiliate_id'];
	?>
	<tr>
		<th colspan="7">
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
		<td><?php echo $waiver['Waiver']['id']; ?>&nbsp;</td>
		<td><?php echo $waiver['Waiver']['name']; ?>&nbsp;</td>
		<td><?php echo $waiver['Waiver']['description']; ?>&nbsp;</td>
		<td><?php __($waiver['Waiver']['active'] ? 'Yes' : 'No'); ?>&nbsp;</td>
		<td><?php echo Configure::read("options.waivers.expiry_type.{$waiver['Waiver']['expiry_type']}"); ?>&nbsp;</td>
		<td><?php
		switch ($waiver['Waiver']['expiry_type']) {
			case 'fixed_dates':
				$months = $this->Form->__generateOptions('month', array('monthNames' => true));
				foreach ($months as $key => $month) {
					unset($months[$key]);
					$months[$key + 0] = $month;
				}
				echo "{$months[$waiver['Waiver']['start_month']]} {$waiver['Waiver']['start_day']} - {$months[$waiver['Waiver']['end_month']]} {$waiver['Waiver']['end_day']}";
				break;

			case 'elapsed_time':
				echo $waiver['Waiver']['duration'] . ' ' . __('days', true);
				break;
		}
		?>&nbsp;</td>
		<td class="actions">
		<?php
		echo $this->ZuluruHtml->iconLink('view_24.png',
			array('action' => 'view', 'waiver' => $waiver['Waiver']['id']),
			array('alt' => __('View', true), 'title' => __('View', true)));
		echo $this->ZuluruHtml->iconLink('edit_24.png',
			array('action' => 'edit', 'waiver' => $waiver['Waiver']['id']),
			array('alt' => __('Edit', true), 'title' => __('Edit', true)));
		echo $this->ZuluruHtml->iconLink('delete_24.png',
			array('action' => 'delete', 'waiver' => $waiver['Waiver']['id']),
			array('alt' => __('Delete', true), 'title' => __('Delete', true)),
			array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $waiver['Waiver']['id'])));
		?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
</div>
<div class="actions">
	<ul>
		<?php
		echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('waiver_add_32.png',
			array('action' => 'add'),
			array('alt' => __('Add', true), 'title' => __('Add Waiver', true))));
		?>
	</ul>
</div>
