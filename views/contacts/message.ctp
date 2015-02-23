<?php
$this->Html->addCrumb (__('Contacts', true));
$this->Html->addCrumb (__('Message', true));
if (isset ($contact)) {
	$this->Html->addCrumb ($contact['Contact']['name']);
}
?>

<div class="contacts form">
<?php echo $this->Form->create('Message', array('url' => Router::normalize($this->here)));?>
	<fieldset>
		<legend><?php __('Message Details'); ?></legend>
	<?php
		if (isset ($contacts)) {
			echo $this->Form->input('contact_id', array(
				'label' => __('To', true),
				'options' => $contacts,
				'empty' => '---',
			));
		} else {
			echo $this->Form->input('To', array(
				'size' => 60,
				'value' => $contact['Contact']['name'],
				'disabled' => true,
			));
			echo $this->Form->hidden('contact_id', array('value' => $contact['Contact']['id']));
		}
		echo $this->Form->input('subject', array('size' => 60));
		echo $this->Form->input('message', array('rows' => 6, 'cols' => 60));
		echo $this->Form->input('cc', array(
			'label' => __('Send a copy to your email address', true),
			'type' => 'checkbox',
		));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
