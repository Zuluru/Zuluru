<?php
$this->Html->addCrumb (__('Team Events', true));
$this->Html->addCrumb ($this->Form->value('Team.name'));
if (isset ($add)) {
	$this->Html->addCrumb (__('Create', true));
} else {
	$this->Html->addCrumb ($this->Form->value('TeamEvent.name'));
	$this->Html->addCrumb (__('Edit', true));
}
?>

<div class="teamEvents form">
<?php echo $this->Form->create('TeamEvent', array('url' => Router::normalize($this->here)));?>
	<fieldset>
 		<legend><?php __('Edit Team Event'); ?></legend>
	<?php
		if (!isset ($add)) {
			echo $this->Form->input('id');
		}
		echo $this->Form->hidden('team_id');
		echo $this->Form->hidden('Team.name');

		echo $this->ZuluruForm->input('name', array('label' => 'Event Name'));
		echo $this->ZuluruForm->input('description');
		if (Configure::read('feature.urls')) {
			echo $this->ZuluruForm->input('website');
		}
		echo $this->ZuluruForm->input('date');
		echo $this->ZuluruForm->input('start');
		echo $this->ZuluruForm->input('end');
		echo $this->ZuluruForm->input('location_name', array('label' => 'Location'));
		echo $this->ZuluruForm->input('location_street', array('label' => 'Address'));
		echo $this->ZuluruForm->input('location_city', array('label' => 'City'));
		echo $this->ZuluruForm->input('location_province', array(
				'label' => 'Province',
				'options' => $provinces,
				'empty' => '---',
		));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>

<?php echo $this->ZuluruHtml->script ('datepicker', array('inline' => false));
