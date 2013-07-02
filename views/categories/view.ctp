<?php
$this->Html->addCrumb (__('Categories', true));
$this->Html->addCrumb ($category['Category']['name']);
$this->Html->addCrumb (__('View', true));
?>

<div class="categories view">
<h2><?php __('Category');?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Name'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $category['Category']['name']; ?>
			&nbsp;
		</dd>
		<?php if (count($affiliates) > 1): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Affiliate'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($category['Affiliate']['name'], array('controller' => 'affiliates', 'action' => 'view', 'affiliate' => $category['Affiliate']['id'])); ?>

		</dd>
		<?php endif; ?>
	</dl>
</div>
<div class="related">
	<h3><?php __('Related Tasks');?></h3>
	<?php if (!empty($category['Task'])):?>
	<table class="list">
	<tr>
		<th><?php __('Name'); ?></th>
		<th><?php __('Reporting To'); ?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
<?php
		$i = 0;
		foreach ($category['Task'] as $task):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
	<tr<?php echo $class;?>>
		<td><?php echo $task['name'];?></td>
		<td><?php echo $this->element('people/block', array('person' => $task['Person']));?></td>
		<td class="actions">
			<?php
			echo $this->ZuluruHtml->iconLink('view_24.png',
				array('controller' => 'tasks', 'action' => 'view', 'task' => $task['id']),
				array('alt' => __('View', true), 'title' => __('View', true)));
			echo $this->ZuluruHtml->iconLink('edit_24.png',
				array('controller' => 'tasks', 'action' => 'edit', 'task' => $task['id']),
				array('alt' => __('Edit', true), 'title' => __('Edit', true)));
			echo $this->ZuluruHtml->iconLink('delete_24.png',
				array('controller' => 'tasks', 'action' => 'delete', 'task' => $task['id']),
				array('alt' => __('Delete', true), 'title' => __('Delete', true)),
				array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $task['id'])));
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
			array('alt' => __('List', true), 'title' => __('List Categories', true))));
		echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('edit_32.png',
			array('action' => 'edit', 'category' => $category['Category']['id']),
			array('alt' => __('Edit', true), 'title' => __('Edit Category', true))));
		echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('delete_32.png',
			array('action' => 'delete', 'category' => $category['Category']['id']),
			array('alt' => __('Delete', true), 'title' => __('Delete Category', true)),
			array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $category['Category']['id']))));
		echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('add_32.png',
			array('action' => 'add'),
			array('alt' => __('Add', true), 'title' => __('Add Category', true))));
		?>
	</ul>
</div>
