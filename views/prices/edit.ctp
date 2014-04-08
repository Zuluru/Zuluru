<?php
$this->Html->addCrumb (__('Price Points', true));
$this->Html->addCrumb ($this->Form->value('Event.name'));
if (isset ($add)) {
	$this->Html->addCrumb (__('Create', true));
} else {
	$this->Html->addCrumb ($this->Form->value('Price.name'));
	$this->Html->addCrumb (__('Edit', true));
}
?>

<div class="prices form">
<?php echo $this->Form->create('Price', array('url' => Router::normalize($this->here))); ?>
	<fieldset>
		<legend><?php printf(__(isset($add) ? 'Create %s' : 'Edit %s', true), __('Price Point', true)); ?></legend>
	<?php
		if (!isset ($add)) {
			echo $this->Form->input('id');
			echo $this->Form->hidden('event_id', array('value' => $this->Form->value('Event.id')));
		} else {
			echo $this->Form->hidden('event_id', array('value' => $event['Event']['id']));
		}
		echo $this->Form->hidden('Event.name', array('value' => $this->Form->value('Event.name')));

		echo $this->Form->input('name', array(
			'size' => 60,
		));
		echo $this->Form->input('description', array(
			'cols' => 70,
			'rows' => 5,
			'after' => $this->Html->para (null, __('This can safely be left blank, if the name is sufficiently descriptive. HTML is allowed.', true)),
			'class' => 'mceAdvanced',
		));
		echo $this->ZuluruForm->input('cost', array(
			'after' => $this->Html->para (null, __('Cost of this event, may be 0, <span class="warning-message">not including tax</span>. If you change the price, anyone who has registered for this but not yet paid will still be changed their original registration price, not the new price. If you need to charge them the new price, close this price point (via the "Closes on" field below), make sure that "Allow Late Payment" is disabled, and add a new price point with the new price.', true)),
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
		echo $this->Form->input('register_rule', array(
			'cols' => 70,
			'after' => $this->Html->para (null, __('Rules that must be passed to allow a person to register for this event.', true) .
				' ' . $this->ZuluruHtml->help(array('action' => 'rules', 'rules'))),
		));
		echo $this->ZuluruForm->input('allow_late_payment', array(
			'options' => Configure::read('options.enable'),
			'empty' => '---',
			'default' => false,
		));
		echo $this->ZuluruForm->input('allow_deposit', array(
			'options' => Configure::read('options.enable'),
			'empty' => '---',
			'default' => false,
		));
		echo $this->ZuluruForm->input('fixed_deposit', array(
			'options' => Configure::read('options.enable'),
			'empty' => '---',
			'default' => false,
		));
		echo $this->ZuluruForm->input('deposit_only', array(
			'options' => Configure::read('options.enable'),
			'empty' => '---',
			'default' => false,
		));
		echo $this->ZuluruForm->input('minimum_deposit', array(
			'after' => $this->Html->para (null, __('Minimum allowable deposit that the registrant must make, if deposits are enabled above. If fixed deposits are selected, this will be the only allowable deposit amount.', true)),
		));
		echo $this->ZuluruForm->input('allow_reservations', array(
			'options' => Configure::read('options.enable'),
			'empty' => '---',
			'default' => false,
		));
		echo $this->ZuluruForm->input('reservation_duration', array(
			'after' => $this->Html->para (null, __('If enabled above, the time in minutes that a reservation will be held before reverting to "Unpaid" status. One day = 1440 minutes.', true)),
		));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>

<?php echo $this->ZuluruHtml->script ('datepicker', array('inline' => false)); ?>
<?php if (Configure::read('feature.tiny_mce')) $this->TinyMce->editor('advanced'); ?>
