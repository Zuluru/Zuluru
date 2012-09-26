<?php
$this->Html->addCrumb (__('Teams', true));
$this->Html->addCrumb (__('Unassigned List', true));
?>

<div class="teams index">
<h2><?php __('List Unassigned Teams');?></h2>
<p>
<?php
echo $this->Paginator->counter(array(
'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
));
?></p>
<table class="list">
<tr>
	<th><?php echo $this->Paginator->sort('name');?></th>
	<th class="actions"><?php __('Actions');?></th>
</tr>
<?php
$i = 0;
foreach ($teams as $team):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $this->Html->link($team['Team']['name'], array('action' => 'view', 'team' => $team['Team']['id'])); ?>
		</td>
		<td class="actions">
			<?php
			echo $this->Html->link(__('Edit', true), array('action' => 'edit', 'team' => $team['Team']['id'], 'return' => true));
			echo $this->Html->link(__('Delete', true), array('action' => 'delete', 'team' => $team['Team']['id'], 'return' => true), null, sprintf(__('Are you sure you want to delete # %s?', true), $team['Team']['id']));
			echo $this->Html->link(__('Move', true), array('action' => 'move', 'team' => $team['Team']['id'], 'return' => true));
			?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
</div>
<div class="paging">
	<?php echo $this->Paginator->prev('<< '.__('previous', true), array(), null, array('class'=>'disabled'));?>
 | 	<?php echo $this->Paginator->numbers();?> | 
	<?php echo $this->Paginator->next(__('next', true).' >>', array(), null, array('class' => 'disabled'));?>
</div>
