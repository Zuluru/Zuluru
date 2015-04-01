<?php
$this->Html->addCrumb (__('People', true));
$this->Html->addCrumb ($this->data['Person']['full_name']);
$this->Html->addCrumb (__('Note', true));
if (empty($this->data['Note']['id'])) {
	$this->Html->addCrumb (__('Add', true));
} else {
	$this->Html->addCrumb (__('Edit', true));
}
?>

<div class="people form">
<h2><?php echo __('Person Note', true) . ': ' . $this->data['Person']['full_name'];?></h2>
<?php
echo $this->Form->create('Note', array('url' => Router::normalize($this->here)));
if (!empty($this->data['Note']['id'])) {
	echo $this->Form->input('id');
}
$options = array(
		VISIBILITY_PRIVATE => __('Only I will be able to see this', true),
);
if ($is_admin) {
	$options[VISIBILITY_ADMIN] = __('Administrators only', true);
}
echo $this->ZuluruForm->input('visibility', array(
		'options' => $options,
		'hide_single' => true,
));
echo $this->ZuluruForm->input('note', array('cols' => 70, 'class' => 'mceSimple'));
echo $this->Form->end(__('Submit', true));
?>
</div>
<?php if (Configure::read('feature.tiny_mce')) $this->TinyMce->editor('simple'); ?>
