<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb ($this->data['Person']['full_name']);
$this->Html->addCrumb (__('Note', true));
if (empty($this->data['Note'][0]['id'])) {
	$this->Html->addCrumb (__('Add', true));
} else {
	$this->Html->addCrumb (__('Edit', true));
}
?>

<p>Anything you enter here will be visible only by you.</p>

<div class="people form">
<?php
echo $this->Form->create('Note', array('url' => Router::normalize($this->here)));
if (!empty($this->data['Note'][0]['id'])) {
	echo $this->Form->input('0.id');
}
echo $this->Form->hidden('Person.full_name');
echo $this->ZuluruForm->input('0.note', array('cols' => 70, 'class' => 'mceSimple'));
echo $this->Form->end(__('Submit', true));
?>
</div>
<?php if (Configure::read('feature.tiny_mce')) $this->TinyMce->editor('simple'); ?>
