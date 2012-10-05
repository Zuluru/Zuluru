<?php
$this->Html->addCrumb (__('Newsletter', true));
$this->Html->addCrumb ($newsletter['Newsletter']['name']);
$this->Html->addCrumb (__('Preview', true));
?>

<div class="newsletters view">
<h2><?php echo $newsletter['Newsletter']['name'] . ' (' . __('Preview', true) . ')';?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Subject'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $newsletter['Newsletter']['subject']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Text'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $newsletter['Newsletter']['text']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Mailing List'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($newsletter['MailingList']['name'], array('controller' => 'mailing_lists', 'action' => 'view', 'mailing_list' => $newsletter['MailingList']['id'])); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Target'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $newsletter['Newsletter']['target']; ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__('Delivery Report', true), array('action' => 'delivery', 'newsletter' => $newsletter['Newsletter']['id'])); ?></li>
		<li><?php echo $this->ZuluruHtml->iconLink('newsletter_send_32.png',
					array('action' => 'send', 'newsletter' => $newsletter['Newsletter']['id']),
					array('alt' => __('Send', true), 'title' => __('Send', true))); ?></li>
		<li><?php echo $this->ZuluruHtml->iconLink('edit_32.png',
					array('action' => 'edit', 'newsletter' => $newsletter['Newsletter']['id'], 'return' => true),
					array('alt' => __('Edit', true), 'title' => __('Edit', true))); ?></li>
		<li><?php echo $this->ZuluruHtml->iconLink('delete_32.png',
					array('action' => 'delete', 'newsletter' => $newsletter['Newsletter']['id']),
					array('alt' => __('Delete', true), 'title' => __('Delete Newsletter', true)),
					array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $newsletter['Newsletter']['id']))); ?></li>
		<li><?php echo $this->Html->link(__('List Newsletters', true), array('action' => 'index')); ?> </li>
		<li><?php echo $this->ZuluruHtml->iconLink('newsletter_add_32.png',
					array('action' => 'add'),
					array('alt' => __('New', true), 'title' => __('New', true))); ?></li>
	</ul>
</div>
