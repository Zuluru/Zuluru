<?php
$this->Html->addCrumb (__('League', true));
$this->Html->addCrumb ($league['League']['long_name']);
$this->Html->addCrumb (__('Add Games', true));
$this->Html->addCrumb (__('Select Type', true));
?>

<div class="schedules add">
<?php echo $this->element('schedule/exclude'); ?>

<p>Please enter some information about the game(s) to create.</p>

<?php
echo $this->Form->create ('Game', array('url' => array('controller' => 'schedules', 'action' => 'add', 'league' => $id)));
$this->data['Game']['step'] = 'type';
echo $this->element('hidden', array('fields' => $this->data));
?>

<fieldset>
<legend>Create a ...</legend>
<?php
echo $this->Form->input('type', array(
		'legend' => false,
		'type' => 'radio',
		'options' => $types,
));
?>

<p>Select the type of game or games to add. Note that for auto-generated schedules, fields will be automatically allocated.
<?php echo $this->ZuluruHtml->help(array('action' => 'schedules', 'add', 'schedule_type', $league['League']['schedule_type'])); ?>
</p>

<?php
echo $this->Form->input('publish', array(
		'label' => __('Publish created games for player viewing?', true),
		'type' => 'checkbox',
));
?>

<p>If this is checked, players will be able to view games immediately after creation. Uncheck it if you wish to make changes before players can view.</p>

<?php
echo $this->Form->input ('double_header', array(
		'label' => __('Allow double-headers?', true),
		'type' => 'checkbox',
		'checked' => false,
));
?>

<p>If this is checked, you will be allowed to schedule more than the expected number of games. Check it only if you need this, as it disables some safety checks.</p>

</fieldset>

<?php echo $this->Form->end(__('Next step', true)); ?>

</div>