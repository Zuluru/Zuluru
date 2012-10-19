<?php
$this->Html->addCrumb (__('Newsletters', true));
$this->Html->addCrumb (__('List', true));
?>

<div class="newsletters index">
	<h2><?php __($current ? 'Recent and Upcoming Newsletters List' : 'Complete Newsletters List');?></h2>
	<table class="list">
	<tr>
		<th><?php echo $this->Paginator->sort('name');?></th>
		<th><?php echo $this->Paginator->sort('mailing_list_id');?></th>
		<th><?php echo $this->Paginator->sort('subject');?></th>
		<th><?php echo $this->Paginator->sort('target');?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
	$i = 0;
	$affiliate_id = null;
	foreach ($newsletters as $newsletter):
		if (count($affiliates) > 1 && $newsletter['MailingList']['affiliate_id'] != $affiliate_id):
			$affiliate_id = $newsletter['MailingList']['affiliate_id'];
	?>
	<tr>
		<th colspan="4">
			<h3 class="affiliate"><?php echo $newsletter['MailingList']['Affiliate']['name']; ?></h3>
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
		<td><?php echo $newsletter['Newsletter']['name']; ?>&nbsp;</td>
		<td><?php echo $newsletter['MailingList']['name']; ?>&nbsp;</td>
		<td><?php echo $newsletter['Newsletter']['subject']; ?>&nbsp;</td>
		<td><?php echo $this->ZuluruTime->date($newsletter['Newsletter']['target']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->ZuluruHtml->iconLink('view_24.png',
					array('action' => 'view', 'newsletter' => $newsletter['Newsletter']['id']),
					array('alt' => __('Preview', true), 'title' => __('Preview', true))); ?>
			<?php echo $this->ZuluruHtml->iconLink('edit_24.png',
					array('action' => 'edit', 'newsletter' => $newsletter['Newsletter']['id']),
					array('alt' => __('Edit', true), 'title' => __('Edit', true))); ?>
			<?php echo $this->Html->link(__('Delivery Report', true), array('action' => 'delivery', 'newsletter' => $newsletter['Newsletter']['id'])); ?>
			<?php echo $this->ZuluruHtml->iconLink('newsletter_send_24.png',
					array('action' => 'send', 'newsletter' => $newsletter['Newsletter']['id']),
					array('alt' => __('Send', true), 'title' => __('Send', true))); ?>
			<?php echo $this->ZuluruHtml->iconLink('delete_24.png',
					array('action' => 'delete', 'newsletter' => $newsletter['Newsletter']['id']),
					array('alt' => __('Delete', true), 'title' => __('Delete Newsletter', true)),
					array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $newsletter['Newsletter']['id']))); ?>
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
<div class="actions">
	<ul>
		<li><?php echo $this->ZuluruHtml->iconLink('newsletter_add_32.png',
					array('action' => 'add'),
					array('alt' => __('New', true), 'title' => __('New', true))); ?></li>
	</ul>
</div>
