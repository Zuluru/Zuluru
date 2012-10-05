<?php
$this->Html->addCrumb (__('Regions', true));
$this->Html->addCrumb ($region['Region']['name']);
$this->Html->addCrumb (__('View', true));
?>

<div class="regions view">
<h2><?php echo $region['Region']['name'];?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Name'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $region['Region']['name']; ?>

		</dd>
		<?php if (count($affiliates) > 1): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Affiliate'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($region['Affiliate']['name'], array('controller' => 'affiliates', 'action' => 'view', 'affiliate' => $region['Affiliate']['id'])); ?>

		</dd>
		<?php endif; ?>
	</dl>
</div>
<div class="related">
	<h3><?php __('Facilities');?></h3>
	<?php if (!empty($region['Facility'])):?>
	<table class="list">
	<tr>
		<th><?php __('Name'); ?></th>
		<th><?php __('Code'); ?></th>
		<th><?php __('Is Open'); ?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
		<?php
		$i = 0;
		foreach ($region['Facility'] as $facility):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
	<tr<?php echo $class;?>>
		<td><?php echo $facility['name'];?></td>
		<td><?php echo $facility['code'];?></td>
		<td><?php __($facility['is_open'] ? 'Yes' : 'No');?></td>
		<td class="actions">
			<?php
			echo $this->ZuluruHtml->iconLink('view_32.png',
				array('controller' => 'facilities', 'action' => 'view', 'facility' => $facility['id']),
				array('alt' => __('View', true), 'title' => __('View', true)));
			echo $this->ZuluruHtml->iconLink('edit_32.png',
				array('controller' => 'facilities', 'action' => 'edit', 'facility' => $facility['id'], 'return' => true),
				array('alt' => __('Edit', true), 'title' => __('Edit', true)));
			echo $this->ZuluruHtml->iconLink('delete_32.png',
				array('controller' => 'facilities', 'action' => 'delete', 'facility' => $facility['id'], 'return' => true),
				array('alt' => __('Delete', true), 'title' => __('Delete', true)),
				array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $facility['id'])));
			?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
<?php endif; ?>

</div>

<div class="actions">
	<ul>
		<?php
		echo $this->Html->tag('li', $this->Html->link(__('New Facility', true),
			array('controller' => 'facilities', 'action' => 'add', 'region' => $region['Region']['id'])));
		echo $this->Html->tag('li', $this->Html->link(__('List Regions', true),
			array('action' => 'index')));
		echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('edit_32.png',
			array('action' => 'edit', 'region' => $region['Region']['id'], 'return' => true),
			array('alt' => __('Edit', true), 'title' => __('Edit Region', true))));
		echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('delete_32.png',
			array('action' => 'delete', 'region' => $region['Region']['id']),
			array('alt' => __('Delete', true), 'title' => __('Delete Region', true)),
			array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $region['Region']['id']))));
		echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('add_32.png',
			array('action' => 'add'),
			array('alt' => __('Add', true), 'title' => __('Add Region', true))));
		?>
	</ul>
</div>
