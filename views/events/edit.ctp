<?php
$this->Html->addCrumb (__('Events', true));
if (isset ($add)) {
	$this->Html->addCrumb (__('Create', true));
} else {
	$this->Html->addCrumb ($this->Form->value('Event.name'));
	$this->Html->addCrumb (__('Edit', true));
}
$collapse = !empty($this->data['Price']['id']);
?>

<div class="events form">
<?php echo $this->Form->create('Event', array('url' => Router::normalize($this->here)));?>
	<fieldset>
		<legend><?php printf(isset($add) ? __('Create %s', true) : __('Edit %s', true), __('Event', true)); ?></legend>
	<?php
		if (isset($clone)):
	?>
		<p class="warning-message"><?php __('You are cloning an event that has multiple price points. Cloning currently only supports a single price point. You will need to add any additional price points after saving this event.'); ?></p>
	<?php
		endif;

		if (!isset ($add)) {
			echo $this->Form->input('id');
			if ($collapse) {
				echo $this->Form->input('Price.id');
			}
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

		if ($collapse || isset ($add)) {
			echo $this->ZuluruForm->input('Price.cost', array(
				'after' => $this->Html->para (null, __('Cost of this event, may be 0, <span class="warning-message">not including tax</span>. If you change the price, anyone who has registered for this but not yet paid will still be charged their original registration price, not the new price. If you need to charge them the new price, close this price point (via the "Closes on" field below), make sure that "Allow Late Payment" is disabled, and add a new price point with the new price.', true)),
			));
			if (Configure::read('payment.tax1_enable')) {
				echo $this->ZuluruForm->input('Price.tax1', array(
					'label' => Configure::read('payment.tax1_name'),
				));
			}
			if (Configure::read('payment.tax2_enable')) {
				echo $this->ZuluruForm->input('Price.tax2', array(
					'label' => Configure::read('payment.tax2_name'),
				));
			}
			echo $this->ZuluruForm->input('Price.open', array(
				'label' => 'Opens on',
				'minYear' => Configure::read('options.year.event.min'),
				'maxYear' => Configure::read('options.year.event.max'),
				'looseYears' => true,
				// TODO: JavaScript link on "12:01AM" to set the time in the inputs
				'after' => $this->Html->para (null, __('The date and time at which registration for this event will open (12:01AM recommended to disambiguate noon from midnight).', true)),
			));
			echo $this->ZuluruForm->input('Price.close', array(
				'label' => 'Closes on',
				'minYear' => Configure::read('options.year.event.min'),
				'maxYear' => Configure::read('options.year.event.max'),
				'looseYears' => true,
				// TODO: JavaScript link on "11:59PM" to set the time in the inputs
				'after' => $this->Html->para (null, __('The date and time at which registration for this event will close (11:59PM recommended to disambiguate midnight from noon).', true)),
			));
			echo $this->ZuluruForm->input('Price.allow_late_payment', array(
				'options' => Configure::read('options.enable'),
				'empty' => '---',
				'default' => false,
			));
			echo $this->ZuluruForm->input('Price.allow_deposit', array(
				'options' => Configure::read('options.enable'),
				'empty' => '---',
				'default' => false,
			));
			echo $this->ZuluruForm->input('Price.fixed_deposit', array(
				'options' => Configure::read('options.enable'),
				'empty' => '---',
				'default' => false,
			));
			echo $this->ZuluruForm->input('Price.deposit_only', array(
				'options' => Configure::read('options.enable'),
				'empty' => '---',
				'default' => false,
			));
			echo $this->ZuluruForm->input('Price.minimum_deposit', array(
				'default' => 0,
				'after' => $this->Html->para (null, __('Minimum allowable deposit that the registrant must make, if deposits are enabled above. If fixed deposits are selected, this will be the only allowable deposit amount.', true)),
			));
			echo $this->ZuluruForm->input('Price.allow_reservations', array(
				'options' => Configure::read('options.enable'),
				'empty' => '---',
				'default' => false,
			));
			echo $this->ZuluruForm->input('Price.reservation_duration', array(
				'default' => 0,
				'after' => $this->Html->para (null, __('If enabled above, the time in minutes that a reservation will be held before reverting to "Unpaid" status. One day = 1440 minutes.', true)),
			));
		}

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
		if ($collapse || isset ($add)) {
			echo $this->Form->input('Price.register_rule', array(
				'cols' => 70,
				'after' => $this->Html->para (null, __('Rules that must be passed to allow a person to register for this event.', true) .
					' ' . $this->ZuluruHtml->help(array('action' => 'rules', 'rules'))),
			));
		}
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

<?php echo $this->ZuluruHtml->script ('datepicker.js', array('inline' => false)); ?>
<?php if (Configure::read('feature.tiny_mce')) $this->TinyMce->editor('advanced'); ?>
