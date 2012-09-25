<?php
$this->Html->addCrumb (__('Holidays', true));
$this->Html->addCrumb (__('List', true));
?>

<div class="holidays index">
	<h2><?php __('Holidays');?></h2>
	<table class="list">
	<tr>
		<th><?php echo $this->Paginator->sort('date');?></th>
		<th><?php echo $this->Paginator->sort('name');?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($holidays as $holiday):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $holiday['Holiday']['date']; ?>&nbsp;</td>
		<td><?php echo $holiday['Holiday']['name']; ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('Edit', true), array('action' => 'edit', 'holiday' => $holiday['Holiday']['id'])); ?>
			<?php echo $this->Html->link(__('Delete', true), array('action' => 'delete', 'holiday' => $holiday['Holiday']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $holiday['Holiday']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
	));
	?>	</p>

	<div class="paging">
		<?php echo $this->Paginator->prev('<< ' . __('previous', true), array(), null, array('class'=>'disabled'));?>
	 | 	<?php echo $this->Paginator->numbers();?>
 |
		<?php echo $this->Paginator->next(__('next', true) . ' >>', array(), null, array('class' => 'disabled'));?>
	</div>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__('New Holiday', true), array('action' => 'add')); ?></li>
	</ul>
</div>