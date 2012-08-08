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

	$url = array('action' => 'delete_document', 'id' => $document['Upload']['id'], 'row' => $rand);
	$url_string = Router::url($url);
	echo $this->Html->link(__('Delete', true), $url,
			array(
				'escape' => false,
				'onClick' => "document_handle_comment('$url_string'); return false;",
			)
	);
	?></td>
</tr>
<?php endforeach; ?>
</table>
</div>

<?php echo $this->element('people/document_div'); ?>
