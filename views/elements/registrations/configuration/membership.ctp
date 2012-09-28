<?php
echo $this->ZuluruForm->input('Event.membership_begins', array(
		'type' => 'date',
		'minYear' => Configure::read('options.year.event.min'),
		'maxYear' => Configure::read('options.year.event.max'),
		'looseYears' => true,
		'after' => $this->Html->para (null, __('First date that this registration will confer membership for (e.g. beginning of the membership year).', true)),
		'required' => true,	// Since this is not in the model validation list, we must force this
));
echo $this->ZuluruForm->input('Event.membership_ends', array(
		'type' => 'date',
		'minYear' => Configure::read('options.year.event.min'),
		'maxYear' => Configure::read('options.year.event.max'),
		'looseYears' => true,
		'after' => $this->Html->para (null, __('Last date that this registration will confer membership for (e.g. end of the membership year).', true)),
		'required' => true,	// Since this is not in the model validation list, we must force this
));
echo $this->ZuluruForm->input('Event.membership_type', array(
		'options' => Configure::read('options.membership_types'),
		'empty' => '---',
		'hide_single' => true,
		'after' => $this->Html->para (null, __('Different membership types may come with limitations (e.g. play on a limited number of teams).', true)),
));
?>
