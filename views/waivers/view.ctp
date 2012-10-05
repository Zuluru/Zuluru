<?php
$this->Html->addCrumb (__('Waivers', true));
$this->Html->addCrumb ($waiver['Waiver']['name']);
$this->Html->addCrumb (__('View', true));
?>

<?php
// Perhaps remove manager status, if we're looking at a different affiliate
if ($is_manager && !in_array($waiver['Waiver']['affiliate_id'], $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
	$is_manager = false;
}
?>

<div class="waivers view">
<h2><?php echo $waiver['Waiver']['name'];?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Name'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $waiver['Waiver']['name']; ?>

		</dd>
		<?php if (count($affiliates) > 1): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Affiliate'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($waiver['Affiliate']['name'], array('controller' => 'affiliates', 'action' => 'view', 'affiliate' => $waiver['Affiliate']['id'])); ?>

		</dd>
		<?php endif; ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Description'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $waiver['Waiver']['description']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Text'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $waiver['Waiver']['text']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Active'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __($waiver['Waiver']['active'] ? 'Yes' : 'No'); ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Expiry Type'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo Configure::read("options.waivers.expiry_type.{$waiver['Waiver']['expiry_type']}"); ?>
			&nbsp;
		</dd>
		<?php if ($waiver['Waiver']['expiry_type'] == 'fixed_dates'): ?>
		<?php
			$months = $this->Form->__generateOptions('month', array('monthNames' => true));
			foreach ($months as $key => $month) {
				unset($months[$key]);
				$months[$key + 0] = $month;
			}
		?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Start'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo "{$months[$waiver['Waiver']['start_month']]} {$waiver['Waiver']['start_day']}"; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('End'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo "{$months[$waiver['Waiver']['end_month']]} {$waiver['Waiver']['end_day']}"; ?>
			&nbsp;
		</dd>
		<?php elseif ($waiver['Waiver']['expiry_type'] == 'elapsed_time'): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('End'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $waiver['Waiver']['duration'] . ' ' . __('days', true); ?>
		</dd>
		<?php endif; ?>
	</dl>
</div>
<div class="actions">
	<ul>
		<?php
		echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('view_32.png',
			array('action' => 'index'),
			array('alt' => __('List', true), 'title' => __('List Waiver', true))));
		if ($is_admin || $is_manager) {
			echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('edit_32.png',
				array('action' => 'edit', 'waiver' => $waiver['Waiver']['id']),
				array('alt' => __('Edit', true), 'title' => __('Edit Waiver', true))));
			echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('delete_32.png',
				array('action' => 'delete', 'waiver' => $waiver['Waiver']['id']),
				array('alt' => __('Edit', true), 'title' => __('Edit Waiver', true)),
				array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $waiver['Waiver']['id']))));
			echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('waiver_add_32.png',
				array('action' => 'add'),
				array('alt' => __('Add', true), 'title' => __('Add Waiver', true))));
		}
		?>
	</ul>
</div>
