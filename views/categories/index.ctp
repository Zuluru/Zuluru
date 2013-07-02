<?php
$this->Html->addCrumb (__('Categories', true));
$this->Html->addCrumb (__('List', true));
?>

<div class="categories index">
<h2><?php __('Categories');?></h2>
<table class="list">
	<tr>
		<th><?php __('Name'); ?></th>
		<th class="actions"><?php __('Actions'); ?></th>
	</tr>
	<?php
	$i = 0;
	$affiliate_id = null;
	foreach ($categories as $category):
		if (count($affiliates) > 1 && $category['Category']['affiliate_id'] != $affiliate_id):
			$affiliate_id = $category['Category']['affiliate_id'];
	?>
	<tr>
		<th colspan="2">
			<h3 class="affiliate"><?php echo $category['Affiliate']['name']; ?></h3>
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
		<td><?php echo $category['Category']['name']; ?>&nbsp;</td>
		<td class="actions">
		<?php
		echo $this->ZuluruHtml->iconLink('view_24.png',
			array('action' => 'view', 'category' => $category['Category']['id']),
			array('alt' => __('View', true), 'title' => __('View', true)));
		echo $this->ZuluruHtml->iconLink('edit_24.png',
			array('action' => 'edit', 'category' => $category['Category']['id']),
			array('alt' => __('Edit', true), 'title' => __('Edit', true)));
		echo $this->ZuluruHtml->iconLink('delete_24.png',
			array('action' => 'delete', 'category' => $category['Category']['id']),
			array('alt' => __('Delete', true), 'title' => __('Delete', true)),
			array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $category['Category']['id'])));
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
				array('alt' => __('Add', true), 'title' => __('Add Category', true))));
		}
		?>
	</ul>
</div>
