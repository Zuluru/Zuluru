<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb (__('Approve Documents', true));
?>

<div class="people documents">
<h2><?php __('Approve Documents'); ?></h2>

<table class="list">
<?php foreach ($documents as $document): ?>
<?php $rand = 'row_' . mt_rand(); ?>
<tr id="<?php echo $rand; ?>">
	<td><?php echo $this->element('people/block', array('person' => $document)); ?></td>
	<td><?php echo $document['UploadType']['name']; ?></td>
	<td class="actions"><?php
	echo $this->Html->link (__('View', true),
			array('action' => 'document', 'id' => $document['Upload']['id']),
			array('target' => 'preview'));
	echo $this->Html->link (__('Approve', true),
			array('action' => 'approve_document', 'id' => $document['Upload']['id']));
	echo $this->Js->link (__('Delete', true),
			array('action' => 'delete_document', 'id' => $document['Upload']['id'], 'row' => $rand),
			array('update' => "#temp_update"));
	?></td>
</tr>
<?php endforeach; ?>
</table>
</div>
