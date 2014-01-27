<?php
$this->Html->addCrumb (__('People', true));
$this->Html->addCrumb (__('List', true));
?>

<div class="people index">
<h2><?php __('People');?></h2>
<table class="list">
	<tr>
		<th><?php echo $this->Paginator->sort('first_name'); ?></th>
		<th><?php echo $this->Paginator->sort('last_name'); ?></th>
		<th><?php __('User Name'); ?></th>
		<th><?php __('Email'); ?></th>
		<th><?php echo $this->Paginator->sort('gender'); ?></th>
		<th><?php echo $this->Paginator->sort('status'); ?></th>
		<th class="actions"><?php __('Actions'); ?></th>
	</tr>
	<?php
	$i = 0;
	$affiliate_id = null;
	foreach ($people as $person):
		if (count($affiliates) > 1 && $person['Affiliate']['id'] != $affiliate_id):
			$affiliate_id = $person['Affiliate']['id'];
	?>
	<tr>
		<th colspan="7">
			<h3 class="affiliate"><?php echo $person['Affiliate']['name']; ?></h3>
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
		<td><?php echo $this->element('people/block', array('person' => $person, 'display_field' => 'first_name')); ?>&nbsp;</td>
		<td><?php echo $this->element('people/block', array('person' => $person, 'display_field' => 'last_name')); ?>&nbsp;</td>
		<td><?php echo $person['Person']['user_name']; ?>&nbsp;</td>
		<td><?php echo $person['Person']['email']; ?>&nbsp;</td>
		<td><?php echo $person['Person']['gender']; ?>&nbsp;</td>
		<td><?php echo $person['Person']['status']; ?>&nbsp;</td>
		<td class="actions">
		<?php
		echo $this->ZuluruHtml->iconLink('view_24.png',
			array('action' => 'view', 'person' => $person['Person']['id']),
			array('alt' => __('View', true), 'title' => __('View', true)));
		echo $this->ZuluruHtml->iconLink('edit_24.png',
			array('action' => 'edit', 'person' => $person['Person']['id']),
			array('alt' => __('Edit', true), 'title' => __('Edit', true)));
		echo $this->ZuluruHtml->iconLink('delete_24.png',
			array('action' => 'delete', 'person' => $person['Person']['id']),
			array('alt' => __('Delete', true), 'title' => __('Delete', true)),
			array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $person['Person']['id'])));
		?>
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
		<?php echo $this->Paginator->prev('<< '.__('previous', true), array(), null, array('class'=>'disabled'));?>
	 | 	<?php echo $this->Paginator->numbers();?>
 |
		<?php echo $this->Paginator->next(__('next', true).' >>', array(), null, array('class' => 'disabled'));?>
	</div>
</div>
