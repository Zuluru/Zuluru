<?php
$this->Html->addCrumb (__('Events', true));
if (isset ($add)) {
	$this->Html->addCrumb (__('Create', true));
} else {
	$this->Html->addCrumb ($this->data['Event']['name']);
	$this->Html->addCrumb (__('Edit', true));
}
?>

<div class="events form">
<?php echo $this->Form->create('Event', array('url' => $this->here));?>
	<fieldset>
 		<legend><?php printf(__('Edit %s', true), __('Event', true)); ?></legend>
	<?php
		if (!isset ($add)) {
			echo $this->Form->input('id');
		}
		echo $this->Form->input('name', array(
			'size' => 70,
			'after' => $this->Html->para (null, __('Full name of this registration event.', true)),
		));
		// TODO: Add JS HTML editor here
		echo $this->Form->input('description', array(
			'cols' => 70,
			'rows' => 5,
			'after' => $this->Html->para (null, __('Complete description of the event, HTML is allowed.', true)),
		));
		echo $this->Form->input('event_type_id', array(
			'empty' => '---',
			'after' => $this->Html->para (null, __('Note that any team type will result in team records being created. If you don\'t want this, then use the appropriate individual type.', true)),
		));
		echo $this->Form->input('waiver_type', array(
			'options' => Configure::read('options.waiver_types'),
			'after' => $this->Html->para (null, __('What type of waiver to require the user to have signed before registering for this event. Memberships should always be set to "membership". Non-member events (those events that don\'t have rules that limit registration to members only) that involve game play should be set to "event". All others should typically be left as "None".', true)),
		));
		echo $this->Form->input('cost', array(
			'after' => $this->Html->para (null, __('Cost of this event, may be 0, <span class="error">not including tax</span>.', true)),
		));
		if (Configure::read('payment.tax1_enable')) {
			echo $this->Form->input('tax1', array(
				'label' => Configure::read('payment.tax1_name'),
			));
		}
		if (Configure::read('payment.tax2_enable')) {
			echo $this->Form->input('tax2', array(
				'label' => Configure::read('payment.tax2_name'),
			));
		}
		echo $this->Form->input('open', array(
			'label' => 'Opens on',
			'minYear' => Configure::read('options.year.event.min'),
			'maxYear' => Configure::read('options.year.event.max'),
			// TODO: JavaScript link on "12:01AM" to set the time in the inputs
			'after' => $this->Html->para (null, __('The date and time at which registration for this event will open (12:01AM recommended to disambiguate noon from midnight).', true)),
		));
		echo $this->Form->input('close', array(
			'label' => 'Closes on',
			'minYear' => Configure::read('options.year.event.min'),
			'maxYear' => Configure::read('options.year.event.max'),
			// TODO: JavaScript link on "11:59PM" to set the time in the inputs
			'after' => $this->Html->para (null, __('The date and time at which registration for this event will close (11:59PM recommended to disambiguate midnight from noon).', true)),
		));
		echo $this->Form->input('cap_male', array(
			'label' => 'Male cap',
			'after' => $this->Html->para (null, __('-1 for no limit.', true)),
		));
		echo $this->Form->input('cap_female', array(
			'label' => 'Female cap',
			'after' => $this->Html->para (null, __('-1 for no limit, -2 to use male cap as combined limit.', true)),
		));
		echo $this->Form->input('multiple', array(
			'label' => 'Allow multiple registrations',
			'after' => $this->Html->para (null, __('Can a single user register for this event multiple times?', true)),
		));
		echo $this->Form->input('questionnaire_id', array(
			'empty' => 'None',
		));
	?>
		<div id="EventTypeFields">
		<?php
		echo $this->element('registration/configuration/' . $event_obj->configurationFieldsElement());
		$this->Js->get('#EventEventTypeId')->event('change', $this->Js->request(
				array('action' => 'event_type_fields'),
				array('update' => '#EventTypeFields', 'dataExpression' => true, 'data' => '$("#EventEventTypeId").get()')
		));
		?>
		</div>
	<?php
		echo $this->Form->input('register_rule', array(
			'cols' => 70,
			'after' => $this->Html->para (null, __('Rules that must be passed to allow a person to register for this event.', true)),
		));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>

<?php echo $this->ZuluruHtml->script ('datepicker', array('inline' => false));
