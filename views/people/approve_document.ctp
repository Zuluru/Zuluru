<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb ($document['Person']['full_name']);
if ($this->action == 'edit_document') {
	$this->Html->addCrumb (__('Edit Document', true));
} else {
	$this->Html->addCrumb (__('Approve Document', true));
}
$this->Html->addCrumb ($document['UploadType']['name']);
?>

<div class="people form">
<?php echo $this->Form->create('Upload', array('url' => Router::normalize($this->here))); ?>
	<fieldset>
<?php
		echo $this->ZuluruForm->hidden('approved', array('value' => true));

		echo $this->ZuluruForm->input('valid_from', array(
			'minYear' => Configure::read('options.year.event.min'),
			'maxYear' => Configure::read('options.year.event.max'),
			'default' => date('Y-m-d', strtotime('Jan 1')),
		));
		echo $this->ZuluruForm->input('valid_until', array(
			'minYear' => Configure::read('options.year.event.min'),
			'maxYear' => Configure::read('options.year.event.max'),
			'default' => date('Y-m-d', strtotime('Dec 31')),
		));
?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>

<?php echo $this->ZuluruHtml->script ('datepicker', array('inline' => false)); ?>
