<?php
$this->Html->addCrumb (__('Upload Types', true));
$this->Html->addCrumb (__('List', true));
?>

<div class="uploadTypes index">
	<h2><?php __('Upload Types');?></h2>
	<table class="list">
	<tr>
		<th><?php __('Id');?></th>
		<th><?php __('Name');?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($uploadTypes as $uploadType):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $uploadType['UploadType']['id']; ?>&nbsp;</td>
		<td><?php echo $uploadType['UploadType']['name']; ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View', true), array('action' => 'view', 'type' => $uploadType['UploadType']['id'])); ?>
			<?php echo $this->Html->link(__('Edit', true), array('action' => 'edit', 'type' => $uploadType['UploadType']['id'])); ?>
			<?php echo $this->Html->link(__('Delete', true), array('action' => 'delete', 'type' => $uploadType['UploadType']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $uploadType['UploadType']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__('New Upload Type', true), array('action' => 'add')); ?></li>
	</ul>
</div>
