<?php
$this->Html->addCrumb (__('Affiliates', true));
$this->Html->addCrumb (__('List', true));
?>

<div class="affiliates index">
<h2><?php __('Affiliates');?></h2>
<table class="list">
	<tr>
		<th><?php __('Name'); ?></th>
		<?php if ($is_admin): ?>
		<th><?php __('Active'); ?></th>
		<th><?php __('Managers'); ?></th>
		<?php endif; ?>
		<th class="actions"><?php __('Actions'); ?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($affiliates as $affiliate):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $affiliate['Affiliate']['name']; ?></td>
		<?php if ($is_admin): ?>
		<td><?php __($affiliate['Affiliate']['active'] ? 'Yes' : 'No'); ?></td>
		<td><?php
		$managers = array();
		foreach ($affiliate['Person'] as $person) {
			$managers[] = $this->element('people/block', compact('person'));
		}
		if (!empty($managers)) {
			echo implode(', ', $managers);
		} else {
			__('None');
		}
		?></td>
		<?php endif; ?>
		<td class="actions">
		<?php
		echo $this->ZuluruHtml->iconLink('view_24.png',
			array('action' => 'view', 'affiliate' => $affiliate['Affiliate']['id']),
			array('alt' => __('View', true), 'title' => __('View', true)));
		if ($is_admin) {
			echo $this->ZuluruHtml->iconLink('edit_24.png',
				array('action' => 'edit', 'affiliate' => $affiliate['Affiliate']['id']),
				array('alt' => __('Edit', true), 'title' => __('Edit', true)));
			echo $this->ZuluruHtml->iconLink('coordinator_add_24.png',
				array('action' => 'add_manager', 'affiliate' => $affiliate['Affiliate']['id']),
				array('alt' => __('Add Manager', true), 'title' => __('Add Manager', true)));
			echo $this->ZuluruHtml->iconLink('delete_24.png',
				array('action' => 'delete', 'affiliate' => $affiliate['Affiliate']['id']),
				array('alt' => __('Delete', true), 'title' => __('Delete', true)),
				array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $affiliate['Affiliate']['id'])));
		}
		?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
</div>
<div class="actions">
	<ul>
		<?php
		if ($is_admin) {
			echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('add_32.png',
				array('action' => 'add'),
				array('alt' => __('Add', true), 'title' => __('Add Affiliate', true))));
		}
		?>
	</ul>
</div>
