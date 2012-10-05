<?php
$this->Html->addCrumb (__('Uload Types', true));
$this->Html->addCrumb ($uploadType['UploadType']['name']);
$this->Html->addCrumb (__('View', true));
?>

<div class="uploadTypes view">
<h2><?php echo $uploadType['UploadType']['name'];?></h2>
	<?php if (count($affiliates) > 1): ?>
	<dl><?php $i = 1; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Affiliate'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($uploadType['Affiliate']['name'], array('controller' => 'affiliates', 'action' => 'view', 'affiliate' => $uploadType['Affiliate']['id'])); ?>

		</dd>
	</dl>
	<?php endif; ?>
<div class="related">
	<h3><?php __('Documents');?></h3>
<?php if (!empty($uploadType['Upload'])): ?>
	<table class="list">
	<tr>
		<th><?php __('Document'); ?></th>
		<th><?php __('Valid From'); ?></th>
		<th><?php __('Valid Until'); ?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($uploadType['Upload'] as $document):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
			$rand = 'row_' . mt_rand();
		?>
		<tr<?php echo $class;?> id="<?php echo $rand; ?>">
			<td><?php echo $this->element('people/block', array('person' => $document));?></td>
<?php if ($document['approved']): ?>
			<td><?php echo $this->ZuluruTime->date($document['valid_from']);?></td>
			<td><?php echo $this->ZuluruTime->date($document['valid_until']);?></td>
<?php else: ?>
			<td colspan="2" class="highlight"><?php __('Unapproved');?></td>
<?php endif; ?>
			<td class="actions">
				<?php echo $this->Html->link(__('View', true), array('controller' => 'people', 'action' => 'document', 'id' => $document['id']), array('target' => 'preview'));?>
<?php if ($document['approved']): ?>
				<?php echo $this->Html->link(__('Edit', true), array('controller' => 'people', 'action' => 'edit_document', 'id' => $document['id']));?>
<?php else: ?>
				<?php echo $this->Html->link(__('Approve', true), array('controller' => 'people', 'action' => 'approve_document', 'id' => $document['id']));?>
<?php endif; ?>
				<?php echo $this->Js->link (__('Delete', true),
					array('controller' => 'people', 'action' => 'delete_document', 'id' => $document['id'], 'row' => $rand),
					array('update' => "#temp_update", 'confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $document['id']))); ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</table>
<?php endif; ?>
</div>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__('Edit Upload Type', true), array('action' => 'edit', 'type' => $uploadType['UploadType']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('Delete Upload Type', true), array('action' => 'delete', 'type' => $uploadType['UploadType']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $uploadType['UploadType']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Upload Types', true), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Upload Type', true), array('action' => 'add')); ?> </li>
	</ul>
</div>
