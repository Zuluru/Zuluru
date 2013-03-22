<?php
$this->Html->addCrumb (__('Mailing List', true));
if (isset ($add)) {
	$this->Html->addCrumb (__('Create', true));
} else {
	$this->Html->addCrumb ($this->Form->value('MailingList.name'));
	$this->Html->addCrumb (__('Edit', true));
}
?>

<div class="mailingLists form">
<?php echo $this->Form->create('MailingList', array('url' => Router::normalize($this->here)));?>
	<fieldset>
 		<legend><?php printf(__(isset($add) ? 'Create %s' : 'Edit %s', true), __('Mailing List', true)); ?></legend>
	<?php
		if (!isset ($add)) {
			echo $this->Form->input('id');
		}
		echo $this->ZuluruForm->input('name', array(
			'size' => 60,
		));
		if (isset ($add)) {
			echo $this->ZuluruForm->input('affiliate_id', array(
				'options' => $affiliates,
				'hide_single' => true,
				'empty' => '---',
			));
		}
		echo $this->ZuluruForm->input('opt_out', array(
			'after' => $this->Html->para (null, __('Check this to allow recipients to unsubscribe from this mailing list. Be sure that your local privacy laws allow you to uncheck this before doing so.', true)),
		));
		echo $this->Form->input('rule', array(
			'cols' => 70,
			'after' => $this->Html->para (null, __('Rules that must be passed to include a person on this mailing list.', true) .
				' ' . $this->ZuluruHtml->help(array('action' => 'rules', 'rules'))),
		));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(sprintf(__('List %s', true), __('Mailing Lists', true)), array('action' => 'index'));?></li>
<?php if (!isset ($add)): ?>
		<li><?php echo $this->ZuluruHtml->iconLink('delete_32.png',
					array('action' => 'delete', 'mailing_list' => $this->Form->value('MailingList.id')),
					array('alt' => __('Delete', true), 'title' => __('Delete Mailing List', true)),
					array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $this->Form->value('MailingList.id')))); ?></li>
		<li><?php echo $this->ZuluruHtml->iconLink('mailing_list_add_32.png',
					array('action' => 'add'),
					array('alt' => __('New', true), 'title' => __('New', true))); ?></li>
<?php endif; ?>
	</ul>
</div>