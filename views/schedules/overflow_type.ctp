<?php
$this->Html->addCrumb (__('League', true));
$this->Html->addCrumb ($league['League']['long_name']);
$this->Html->addCrumb (__('Add Games', true));
$this->Html->addCrumb (__('Select Additional Bracket Type', true));
?>

<div class="schedules add">

<p>You are scheduling a tournament with an additional bracket to hold "overflow" teams. What type of schedule should be created for this bracket?</p>

<?php
echo $this->Form->create ('Game', array('url' => Router::normalize($this->here)));
$this->data['Game']['step'] = 'subtype';
echo $this->element('hidden', array('fields' => $this->data));
?>

<fieldset>
<legend>Create a ...</legend>
<?php
echo $this->Form->input('subtype', array(
		'legend' => false,
		'type' => 'radio',
		'options' => $types,
));
?>

</fieldset>

<?php echo $this->Form->end(__('Next step', true)); ?>

</div>