<?php
$this->Html->addCrumb (__('Contacts', true));
if (isset ($add)) {
	$this->Html->addCrumb (__('Create', true));
} else {
	$this->Html->addCrumb ($this->Form->value('Contact.name'));
	$this->Html->addCrumb (__('Edit', true));
}
?>

<div class="contacts form">
<?php echo $this->Form->create('Contact', array('url' => Router::normalize($this->here)));?>
	<fieldset>
		<legend><?php __('Contact Details'); ?></legend>
	<?php
		if (!isset ($add)) {
			echo $this->Form->input('id');
		}
		echo $this->ZuluruForm->input('name', array(
			'after' => $this->Html->para (null, __('The name of your contact.', true)),
		));
		echo $this->ZuluruForm->input('email', array(
			'after' => $this->Html->para (null, __('The email address for your contact. This will not be shown to users, only used to deliver messages.', true)),
		));
		if (isset ($add)) {
			echo $this->ZuluruForm->input('affiliate_id', array(
				'options' => $affiliates,
				'hide_single' => true,
				'empty' => '---',
			));
		}
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
