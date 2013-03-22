<?php
$this->Html->addCrumb (__('Events', true));
if (isset ($add)) {
	$this->Html->addCrumb (__('Create', true));
} else {
	$this->Html->addCrumb ($this->Form->value('Event.name'));
	$this->Html->addCrumb (__('Edit', true));
}
?>

<div class="events form">
<?php echo $this->Form->create('Event', array('url' => Router::normalize($this->here)));?>
	<fieldset>
 		<legend><?php printf(__(isset($add) ? 'Create %s' : 'Edit %s', true), __('Event', true)); ?></legend>
	<?php
		if (!isset ($add)) {
			echo $this->Form->input('id');
		}
		echo $this->ZuluruForm->input('name', array(
			'size' => 70,
			'after' => $this->Html->para (null, __('Full name of this registration event.', true)),
		));
		if (isset ($add)) {
			echo $this->ZuluruForm->input('affiliate_id', array(
				'options' => $affiliates,
				'hide_single' => true,
				'empty' => '---',
			));
		} else {
			echo $this->ZuluruForm->hidden('affiliate_id');
		}
		echo $this->ZuluruForm->input('description', array(
			'cols' => 70,
			'rows' => 5,
			'after' => $this->Html->para (null, __('Complete description of the event, HTML is allowed.', true)),
			'class' => 'mceAdvanced',
		));
		echo $this->ZuluruForm->input('event_type_id', array(
			'empty' => '---',
			'after' => $this->Html->para (null, __('Note that any team type will result in team records being created. If you don\'t want this, then use the appropriate individual type.', true)),
		));
		echo $this->ZuluruForm->input('cost', array(
			'after' => $this->Html->para (null, __('Cost of this event, may be 0, <span class="error">not including tax</span>.', true)),
		));
		if (Configure::read('payment.tax1_enable')) {
			echo $this->ZuluruForm->input('tax1', array(
				'label' => Configure::read('payment.tax1_name'),
			));
		}
		if (Configure::read('payment.tax2_enable')) {
			echo $this->ZuluruForm->input('tax2', array(
				'label' => Configure::read('payment.tax2_name'),
			));
		}
		echo $this->ZuluruForm->input('open', array(
			'label' => 'Opens on',
			'minYear' => Configure::read('options.year.event.min'),
			'maxYear' => Configure::read('options.year.event.max'),
			'looseYears' => true,
			// TODO: JavaScript link on "12:01AM" to set the time in the inputs
			'after' => $this->Html->para (null, __('The date and time at which registration for this event will open (12:01AM recommended to disambiguate noon from midnight).', true)),
		));
		echo $this->ZuluruForm->input('close', array(
			'label' => 'Closes on',
			'minYear' => Configure::read('options.year.event.min'),
			'maxYear' => Configure::read('options.year.event.max'),
			'looseYears' => true,
			// TODO: JavaScript link on "11:59PM" to set the time in the inputs
			'after' => $this->Html->para (null, __('The date and time at which registration for this event will close (11:59PM recommended to disambiguate midnight from noon).', true)),
		));
		echo $this->ZuluruForm->input('cap_male', array(
			'label' => 'Male cap',
			'after' => $this->Html->para (null, __('-1 for no limit.', true)),
		));
		echo $this->ZuluruForm->input('cap_female', array(
			'label' => 'Female cap',
			'after' => $this->Html->para (null, __('-1 for no limit, -2 to use male cap as combined limit.', true)),
		));
		echo $this->ZuluruForm->input('multiple', array(
			'label' => 'Allow multiple registrations',
			'after' => $this->Html->para (null, __('Can a single user register for this event multiple times?', true)),
		));
		echo $this->ZuluruForm->input('questionnaire_id', array(
			'empty' => 'None',
		));
	?>
		<div id="EventTypeFields">
		<?php
		if (!isset($add)) {
			$affiliates = array($this->Form->value('Event.affiliate_id') => $affiliates[$this->Form->value('Event.affiliate_id')]);
		}
		echo $this->element('registrations/configuration/' . $event_obj->configurationFieldsElement(), compact('affiliates'));
		$this->Js->get('#EventEventTypeId')->event('change', $this->Js->request(
				array('action' => 'event_type_fields'),
				array('update' => '#EventTypeFields', 'dataExpression' => true, 'data' => 'jQuery("#EventEventTypeId").get()')
		));
		?>
		</div>
	<?php
		echo $this->Form->input('register_rule', array(
			'cols' => 70,
			'after' => $this->Html->para (null, __('Rules that must be passed to allow a person to register for this event.', true) .
				' ' . $this->ZuluruHtml->help(array('action' => 'rules', 'rules'))),
		));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<?php if (!isset ($add)): ?>
<div class="actions">
	<ul>
		<li><?php
		$alt = sprintf(__('Manage %s', true), __('Connections', true));
		echo $this->ZuluruHtml->iconLink('connections_24.png',
			array('action' => 'connections', 'event' => $this->Form->value('Event.id')),
			array('alt' => $alt, 'title' => $alt));
		?> </li>
	</ul>
</div>
<?php endif; ?>

<?php echo $this->ZuluruHtml->script ('datepicker', array('inline' => false)); ?>
<?php if (Configure::read('feature.tiny_mce')) $this->TinyMce->editor('advanced'); ?>
