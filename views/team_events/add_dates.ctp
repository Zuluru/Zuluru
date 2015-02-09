<?php
$this->Html->addCrumb (__('Team Events', true));
$this->Html->addCrumb ($this->Form->value('Team.name'));
$this->Html->addCrumb (__('Create', true));
$this->Html->addCrumb (__('Dates', true));
?>

<div class="teamEvents form">
<?php echo $this->Form->create('TeamEvent', array('url' => Router::normalize($this->here)));?>
	<fieldset>
		<legend><?php __('Team Event Dates'); ?></legend>
	<?php
	for ($i = 0; $i < $this->data['TeamEvent']['repeat_count']; ++ $i) {
		echo $this->Form->input("TeamEvent.Dates.$i.date", array('type' => 'date'));
	}
	unset($this->data['TeamEvent']['Dates']);
	echo $this->element('hidden', array('fields' => $this->data));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<?php
echo $this->ZuluruHtml->script ('datepicker.js', array('inline' => false));
?>
