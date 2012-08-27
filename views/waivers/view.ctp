<?php
$this->Html->addCrumb (__('Waivers', true));
$this->Html->addCrumb ($waiver['Waiver']['name']);
$this->Html->addCrumb (__('View', true));
?>

<div class="waivers view">
<h2><?php __('Waiver');?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Name'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $waiver['Waiver']['name']; ?>
			&nbsp;
		</dd>
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
			<?php echo $waiver['Waiver']['active']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Expiry Type'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $waiver['Waiver']['expiry_type']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Start'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $waiver['Waiver']['start']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('End'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $waiver['Waiver']['end']; ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<ul>
		<?php
		echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('view_32.png',
			array('action' => 'index'),
			array('alt' => __('List', true), 'title' => __('List Waiver', true))));
		if ($is_admin) {
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
