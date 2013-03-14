<?php
$this->Html->addCrumb (__('Mailing List', true));
$this->Html->addCrumb ($mailingList['MailingList']['name']);
$this->Html->addCrumb (__('View', true));
?>

<div class="mailingLists view">
<h2><?php echo $mailingList['MailingList']['name'];?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Name'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $mailingList['MailingList']['name']; ?>
		</dd>
		<?php if (count($affiliates) > 1): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Affiliate'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($mailingList['Affiliate']['name'], array('controller' => 'affiliates', 'action' => 'view', 'affiliate' => $mailingList['Affiliate']['id'])); ?>

		</dd>
		<?php endif; ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Opt Out'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __($mailingList['MailingList']['opt_out'] ? 'Yes' : 'No'); ?>
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Rule'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<pre><?php echo $mailingList['MailingList']['rule']; ?></pre>
		</dd>
	</dl>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->ZuluruHtml->iconLink('edit_32.png',
					array('action' => 'edit', 'mailing_list' => $mailingList['MailingList']['id'], 'return' => true),
					array('alt' => __('Edit', true), 'title' => __('Edit', true))); ?></li>
		<li><?php echo $this->ZuluruHtml->iconLink('email_32.png',
					array('action' => 'preview', 'mailing_list' => $mailingList['MailingList']['id']),
					array('alt' => __('Preview', true), 'title' => __('Preview', true))); ?></li>
		<li><?php echo $this->ZuluruHtml->iconLink('delete_32.png',
					array('action' => 'delete', 'mailing_list' => $mailingList['MailingList']['id']),
					array('alt' => __('Delete', true), 'title' => __('Delete Mailing List', true)),
					array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $mailingList['MailingList']['id']))); ?></li>
		<li><?php echo $this->Html->link(__('List Mailing Lists', true), array('action' => 'index')); ?> </li>
		<li><?php echo $this->ZuluruHtml->iconLink('mailing_list_add_32.png',
					array('action' => 'add'),
					array('alt' => __('New', true), 'title' => __('New', true))); ?></li>
	</ul>
</div>

<div class="related">
	<h3><?php __('Related Newsletters');?></h3>
	<?php if (!empty($mailingList['Newsletter'])):?>
	<table class="list">
	<tr>
		<th><?php __('Name'); ?></th>
		<th><?php __('Target'); ?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($mailingList['Newsletter'] as $newsletter):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $newsletter['name'];?></td>
			<td><?php echo $this->ZuluruTime->date($newsletter['target']);?></td>
			<td class="actions">
				<?php echo $this->ZuluruHtml->iconLink('view_24.png',
						array('controller' => 'newsletters', 'action' => 'view', 'newsletter' => $newsletter['id']),
						array('alt' => __('Preview', true), 'title' => __('Preview', true))); ?>
				<?php echo $this->ZuluruHtml->iconLink('edit_24.png',
						array('controller' => 'newsletters', 'action' => 'edit', 'newsletter' => $newsletter['id'], 'return' => true),
						array('alt' => __('Edit', true), 'title' => __('Edit', true))); ?>
				<?php echo $this->Html->link(__('Delivery Report', true), array('action' => 'delivery', 'newsletter' => $newsletter['id'])); ?>
				<?php echo $this->ZuluruHtml->iconLink('newsletter_send_24.png',
						array('controller' => 'newsletters', 'action' => 'send', 'newsletter' => $newsletter['id']),
						array('alt' => __('Send', true), 'title' => __('Send', true))); ?>
				<?php echo $this->ZuluruHtml->iconLink('delete_24.png',
						array('controller' => 'newsletters', 'action' => 'delete', 'newsletter' => $newsletter['id'], 'return' => true),
						array('alt' => __('Delete', true), 'title' => __('Delete Newsletter', true)),
						array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $newsletter['id']))); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

</div>
