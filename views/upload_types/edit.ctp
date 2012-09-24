<?php
$this->Html->addCrumb (__('Upload Type', true));
if (isset ($add)) {
	$this->Html->addCrumb (__('Create', true));
} else {
	$this->Html->addCrumb ($this->data['UploadType']['name']);
	$this->Html->addCrumb (__('Edit', true));
}
?>

<div class="uploadTypes form">
<?php echo $this->Form->create('UploadType', array('url' => Router::normalize($this->here)));?>
	<fieldset>
 		<legend><?php printf(__(isset($add) ? 'Create %s' : 'Edit %s', true), __('Upload Type', true)); ?></legend>
	<?php
		if (!isset ($add)) {
			echo $this->Form->input('id');
		}
		echo $this->Form->input('name');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>