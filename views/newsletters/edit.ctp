<?php
$this->Html->addCrumb (__('Newsletter', true));
if (isset ($add)) {
	$this->Html->addCrumb (__('Create', true));
} else {
	$this->Html->addCrumb ($this->Form->value('Newsletter.name'));
	$this->Html->addCrumb (__('Edit', true));
}
?>

<div class="newsletters form">
<?php echo $this->Form->create('Newsletter', array('url' => Router::normalize($this->here)));?>
	<fieldset>
 		<legend><?php printf(__(isset($add) ? 'Create %s' : 'Edit %s', true), __('Newsletter', true)); ?></legend>
	<?php
		if (!isset ($add)) {
			echo $this->Form->input('id');
		}
		echo $this->ZuluruForm->input('name', array(
			'size' => 60,
			'after' => $this->Html->para (null, __('A short name for this newsletter, to be used as a heading in administrative reports.', true)),
		));
		echo $this->ZuluruForm->input('Newsletter.mailing_list_id', array('empty' => 'Select one:'));
		echo $this->ZuluruForm->input('from', array(
			'size' => 60,
			'after' => $this->Html->para (null, __('Email address that this newsletter should come from.', true)),
		));
		echo $this->ZuluruForm->input('to', array(
			'size' => 60,
			'after' => $this->Html->para (null, __('Email address that this newsletter should be sent to, if different than the From address. If the "Personalize" box is checked, this is ignored.', true)),
		));
		echo $this->ZuluruForm->input('reply_to', array(
			'size' => 60,
			'after' => $this->Html->para (null, __('Email address that replies to this newsletter should be sent to, if different than the From address.', true)),
		));
		echo $this->ZuluruForm->input('subject', array(
			'size' => 60,
			'after' => $this->Html->para (null, __('Subject line for emailing this newsletter.', true)),
		));
		echo $this->ZuluruForm->input('text', array(
			'cols' => 60,
			'rows' => 30,
			'after' => $this->Html->para (null, __('The full text of the newsletter.', true)),
			'class' => 'mceNewsletter',
		));
		echo $this->ZuluruForm->input('target', array(
			'minYear' => Configure::read('options.year.event.min'),
			'maxYear' => Configure::read('options.year.event.max'),
			'looseYears' => true,
			'after' => $this->Html->para (null, __('Target date for sending this newsletter. For display purposes only; does not cause the newsletter to be sent on this date.', true)),
		));
		echo $this->ZuluruForm->input('delay', array(
			'after' => $this->Html->para (null, __('Time (in minutes) between batches. Larger delays decrease the chance that sites like Hotmail will consider your email to be spam.', true)),
		));
		echo $this->ZuluruForm->input('batch_size', array(
			'after' => $this->Html->para (null, __('Maximum number of newsletters to send in a single batch. Smaller batches decrease the chance that sites like Hotmail will consider your email to be spam.', true)),
		));
		echo $this->ZuluruForm->input('personalize', array(
			'after' => $this->Html->para (null, __('Check this to personalize each email. This slows down the sending process and increases the amount of internet traffic your newsletter will generate.', true)),
		));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(sprintf(__('List %s', true), __('Newsletters', true)), array('action' => 'index'));?></li>
<?php if (!isset ($add)): ?>
		<li><?php echo $this->Html->link(__('Delivery Report', true), array('action' => 'delivery', 'newsletter' => $this->Form->value('Newsletter.id'))); ?></li>
		<li><?php echo $this->ZuluruHtml->iconLink('newsletter_send_32.png',
					array('action' => 'send', 'newsletter' => $this->Form->value('Newsletter.id')),
					array('alt' => __('Send', true), 'title' => __('Send', true))); ?></li>
		<li><?php echo $this->ZuluruHtml->iconLink('delete_32.png',
					array('action' => 'delete', 'newsletter' => $this->Form->value('Newsletter.id')),
					array('alt' => __('Delete', true), 'title' => __('Delete Newsletter', true)),
					array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $this->Form->value('Newsletter.id')))); ?></li>
<?php else: ?>
		<li><?php echo $this->ZuluruHtml->iconLink('newsletter_add_32.png',
					array('action' => 'add'),
					array('alt' => __('New', true), 'title' => __('New', true))); ?></li>
<?php endif; ?>
	</ul>
</div>

<?php echo $this->ZuluruHtml->script ('datepicker', array('inline' => false)); ?>
<?php if (Configure::read('feature.tiny_mce')) $this->TinyMce->editor('newsletter'); ?>
