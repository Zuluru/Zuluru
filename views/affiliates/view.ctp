<?php
$this->Html->addCrumb (__('Affiliates', true));
$this->Html->addCrumb ($affiliate['Affiliate']['name']);
$this->Html->addCrumb (__('View', true));
?>

<div class="affiliates view">
<h2><?php __('Affiliate');?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Name'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $affiliate['Affiliate']['name']; ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="related">
	<h3><?php __('Managers');?></h3>
	<?php if (!empty($affiliate['Person'])):?>
	<table class="list">
	<tr>
		<th><?php __('User Name'); ?></th>
		<th><?php __('First Name'); ?></th>
		<th><?php __('Last Name'); ?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
<?php
		$i = 0;
		foreach ($affiliate['Person'] as $person):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
	<tr<?php echo $class;?>>
		<td><?php echo $person['user_name'];?></td>
		<td><?php echo $person['first_name'];?></td>
		<td><?php echo $person['last_name'];?></td>
		<td class="actions">
			<?php
			echo $this->ZuluruHtml->iconLink('view_32.png',
				array('controller' => 'people', 'action' => 'view', 'person' => $person['id']),
				array('alt' => __('View', true), 'title' => __('View', true)));
			if ($is_admin) {
				echo $this->ZuluruHtml->iconLink('edit_32.png',
					array('controller' => 'people', 'action' => 'edit', 'person' => $person['id']),
					array('alt' => __('Edit', true), 'title' => __('Edit', true)));
				echo $this->ZuluruHtml->iconLink('coordinator_delete_32.png',
					array('action' => 'remove_manager', 'affiliate' => $affiliate['Affiliate']['id'], 'person' => $person['id']),
					array('alt' => __('Remove', true), 'title' => __('Remove', true)));
			}
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
		echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('view_32.png',
			array('action' => 'index'),
			array('alt' => __('List', true), 'title' => __('List Affiliates', true))));
		if ($is_admin) {
			echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('edit_32.png',
				array('action' => 'edit', 'affiliate' => $affiliate['Affiliate']['id']),
				array('alt' => __('Edit', true), 'title' => __('Edit Affiliate', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('coordinator_add_32.png',
				array('action' => 'add_manager', 'affiliate' => $affiliate['Affiliate']['id']),
				array('alt' => __('Add Manager', true), 'title' => __('Add Manager', true))));
			echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('delete_32.png',
				array('action' => 'delete', 'affiliate' => $affiliate['Affiliate']['id']),
				array('alt' => __('Edit', true), 'title' => __('Edit Affiliate', true)),
				array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $affiliate['Affiliate']['id']))));
		}
		?>
	</ul>
</div>
