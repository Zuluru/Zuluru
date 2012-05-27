<?php
$this->Html->addCrumb (__('Division', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('Add Games', true));
$this->Html->addCrumb (__('Select Additional Bracket Type', true));
?>

<div class="schedules add">

<p>You are scheduling a tournament with an additional bracket to hold "overflow" teams. What type of schedule should be created for this bracket?</p>

<?php
echo $this->Form->create ('Game', array('url' => Router::normalize($this->here)));
$this->data['Game']['step'] = 'overflow_type';
echo $this->element('hidden', array('fields' => $this->data));
?>

<fieldset>
<legend>Create a ...</legend>
<?php
echo $this->Form->input('overflow_type', array(
		'legend' => false,
		'type' => 'radio',
		'options' => $types,
));
?>

</fieldset>

<?php echo $this->Form->end(__('Next step', true)); ?>

</div>