<?php
$this->Html->addCrumb (__('Division', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('Add Games', true));
$this->Html->addCrumb (__('Select Bracket Type', true));
?>

<div class="schedules add">

<p>You are scheduling a tournament with a bracket size that has multiple scheduling options. What type of schedule should be created for these brackets?</p>

<?php
echo $this->Form->create ('Game', array('url' => Router::normalize($this->here)));
$this->data['Game']['step'] = 'bracket_type';
echo $this->element('hidden', array('fields' => $this->data));
?>

<fieldset>
<legend>Create a ...</legend>
<?php
echo $this->Form->input('bracket_type', array(
		'legend' => false,
		'type' => 'radio',
		'options' => $types,
));
?>

</fieldset>

<?php echo $this->Form->end(__('Next step', true)); ?>

</div>