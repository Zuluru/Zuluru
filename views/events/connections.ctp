<?php
$this->Html->addCrumb (__('Event', true));
$this->Html->addCrumb ($this->data['Event']['name']);
$this->Html->addCrumb (__('Connections', true));
?>

<div class="events connections form">
<?php echo $this->Form->create('Event', array('url' => Router::normalize($this->here)));?>
	<fieldset>
	<legend><?php echo __('Event Connections', true) . ': ' . $this->data['Event']['name'];?></legend>
<?php
	echo $this->Form->input('id');
	echo $this->Form->hidden('name');
	echo $this->Form->hidden('open');
	echo $this->Form->hidden('close');

	// Limit lists of events by open and close dates
	$before = $after = $alternate = array_fill_keys(array_values($event_types), array());
	foreach ($events as $event) {
		$type = $event_types[$event['Event']['event_type_id']];
		if ($event['Event']['open'] < $this->data['Event']['open']) {
			$before[$type][$event['Event']['id']] = $event['Event']['name'];
		}
		if ($event['Event']['close'] > $this->data['Event']['close']) {
			$after[$type][$event['Event']['id']] = $event['Event']['name'];
		}
		if ($event['Event']['close'] > $this->data['Event']['open'] && $event['Event']['open'] < $this->data['Event']['close']) {
			$alternate[$type][$event['Event']['id']] = $event['Event']['name'];
		}
	}
?>

		<fieldset>
<?php
	__('These two lists connect this event to events that have gone before. They will typically be the same. For more details see the help for each field.');
	echo $this->ZuluruForm->input('Event.Predecessor', array(
			'label' => __('Events to consider as predecessors to this one:', true),
			'options' => $before,
			'multiple' => true,
			'title' => __('Select all that apply', true),
	));
	echo $this->ZuluruForm->input('Event.SuccessorTo', array(
			'label' => __('Events that this one is considered a successor to:', true),
			'options' => $before,
			'multiple' => true,
			'title' => __('Select all that apply', true),
	));
?>
		</fieldset>

		<fieldset>
<?php
	__('These two lists connect this event to events that come later, and are generally not applicable when creating a new event. They will typically be the same. For more details see the help for each field.');
	echo $this->ZuluruForm->input('Event.PredecessorTo', array(
			'label' => __('Events that this one is considered a predecessor to:', true),
			'options' => $after,
			'multiple' => true,
			'title' => __('Select all that apply', true),
	));
	echo $this->ZuluruForm->input('Event.Successor', array(
			'label' => __('Events to consider as successors to this one:', true),
			'options' => $after,
			'multiple' => true,
			'title' => __('Select all that apply', true),
	));
?>
		</fieldset>

		<fieldset>
<?php
	echo $this->ZuluruForm->input('Event.Alternate', array(
			'label' => __('Events to consider as alternates to this one:', true),
			'options' => $alternate,
			'multiple' => true,
			'title' => __('Select all that apply', true),
	));
?>
		</fieldset>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<?php
$this->ZuluruHtml->css('jquery.asmselect', null, array('inline' => false));
$this->ZuluruHtml->script('jquery.asmselect', array('inline' => false));
$this->Js->buffer('jQuery("select[multiple]").asmSelect();');
?>