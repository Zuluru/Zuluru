<?php
$this->Html->addCrumb (__('Mailing Lists', true));
$this->Html->addCrumb (__('List', true));
?>

<div class="mailingLists index">
	<h2><?php __('Mailing Lists');?></h2>
	<table class="list">
	<tr>
		<th><?php echo $this->Paginator->sort('name');?></th>
		<th><?php echo $this->Paginator->sort('opt_out');?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($mailingLists as $mailingList):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $mailingList['MailingList']['name']; ?></td>
		<td><?php __($mailingList['MailingList']['opt_out'] ? 'Yes' : 'No'); ?></td>
		<td class="actions">
			<?php echo $this->ZuluruHtml->iconLink('view_24.png',
					array('action' => 'view', 'mailing_list' => $mailingList['MailingList']['id']),
					array('alt' => __('View', true), 'title' => __('View', true))); ?>
			<?php echo $this->ZuluruHtml->iconLink('edit_24.png',
					array('action' => 'edit', 'mailing_list' => $mailingList['MailingList']['id']),
					array('alt' => __('Edit', true), 'title' => __('Edit', true))); ?>
			<?php echo $this->ZuluruHtml->iconLink('email_24.png',
					array('action' => 'preview', 'mailing_list' => $mailingList['MailingList']['id']),
					array('alt' => __('Preview', true), 'title' => __('Preview', true))); ?>
			<?php echo $this->ZuluruHtml->iconLink('delete_24.png',
					array('action' => 'delete', 'mailing_list' => $mailingList['MailingList']['id']),
					array('alt' => __('Delete', true), 'title' => __('Delete Mailing List', true)),
					array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $mailingList['MailingList']['id']))); ?>
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
		<li><?php echo $this->ZuluruHtml->iconLink('mailing_list_add_32.png',
					array('action' => 'add'),
					array('alt' => __('New', true), 'title' => __('New', true))); ?></li>
	</ul>
</div>
