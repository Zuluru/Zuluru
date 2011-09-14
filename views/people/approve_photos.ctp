<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb (__('Approve Photos', true));
?>

<div class="people photos">
<h2><?php __('Approve Photos'); ?></h2>

<table class="list">
<?php foreach ($photos as $photo): ?>
<?php $rand = 'row_' . mt_rand(); ?>
<tr id="<?php echo $rand; ?>">
	<td><?php echo $this->Html->link ($photo['Person']['full_name'], array('controller' => 'people', 'action' => 'view', 'person' => $photo['Person']['id'])); ?></td>
	<td><?php echo $this->element('people/player_photo', array('person' => $photo['Person'], 'upload' => $photo['Upload'])); ?></td>
	<td class="actions"><?php
	echo $this->Js->link (__('Approve', true),
			array('controller' => 'people', 'action' => 'approve_photo', 'person' => $photo['Person']['id'], 'id' => $photo['Upload']['id'], 'row' => $rand),
			array('update' => "#temp_update"));
	echo $this->Js->link (__('Delete', true),
			array('controller' => 'people', 'action' => 'delete_photo', 'person' => $photo['Person']['id'], 'id' => $photo['Upload']['id'], 'row' => $rand),
			array('update' => "#temp_update"));
	?></td>
</tr>
<?php endforeach; ?>
</table>
</div>
<div id="temp_update" style="display: none;"></div>
