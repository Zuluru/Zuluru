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
		<legend><?php printf(isset($add) ? __('Create %s', true) : __('Edit %s', true), __('Team Event', true)); ?></legend>
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

		if (isset ($add)) {
			echo $this->ZuluruForm->input('repeat', array(
				'type' => 'checkbox',
				'label' => __('This is a repeating event', true),
			));

			$this->Js->get('#TeamEventRepeat')->event('change', 'repeatChanged();');
			$this->Js->buffer('repeatChanged();');
?>
		<fieldset id="RepeatDetails">
			<legend><?php __('Event Repetition Details'); ?></legend>
<?php
			echo $this->ZuluruForm->input('repeat_count', array(
					'label' => __('Number of events to create', true),
					'size' => 6,
			));
			echo $this->ZuluruForm->input('repeat_type', array(
					'label' => __('Create events', true),
					'options' => array(
						'weekly' => __('Once a week on the same day', true),
						'daily' => __('Every day', true),
						'weekdays' => __('Every weekday', true),
						'weekends' => __('Every Saturday and Sunday', true),
						'custom' => __('On days that I will specify', true),
					),
			));
?>
		</fieldset>
<?php
		}

		echo $this->ZuluruForm->input('location_name', array('label' => __('Location', true)));
		echo $this->ZuluruForm->input('location_street', array('label' => __('Address', true)));
		echo $this->ZuluruForm->input('location_city', array('label' => __('City', true)));
		echo $this->ZuluruForm->input('location_province', array(
				'label' => __('Province', true),
				'options' => $provinces,
				'empty' => '---',
		));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>

<?php
echo $this->ZuluruHtml->script ('datepicker', array('inline' => false));

echo $this->Html->scriptBlock('
function repeatChanged() {
	var checked = jQuery("#TeamEventRepeat").prop("checked");
	if (checked) {
		jQuery("#RepeatDetails").css("display", "");
	} else {
		jQuery("#RepeatDetails").css("display", "none");
	}
}
');
