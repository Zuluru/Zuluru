<?php
$this->Html->addCrumb (__('Division', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('Add Games', true));
$this->Html->addCrumb (__('Select Date', true));
?>

<div class="schedules add">
<?php echo $this->element('schedules/exclude'); ?>

<?php
echo $this->Form->create ('Game', array('url' => Router::normalize($this->here)));
$saved_data = $this->data;
$this->data['Game']['step'] = 'date';
echo $this->element('hidden', array('fields' => $this->data));
?>

<fieldset>
<legend><?php __('Select desired start date'); ?></legend>

<p><?php
printf (__('Scheduling a %s will create a total of ', true), $desc);
if (count($num_fields) > 1) {
	// A simple array with multiple elements means that multiple time slots are required.
	$total_fields = array_sum($num_fields);
	$min_slots = count($num_fields);
	printf (__('%d games across a minimum of %d time slots.', true), $total_fields, $min_slots);
} else {
	// A simple array with one element means games may happen in a single time slot.
	$total_fields = array_sum($num_fields);
	printf (__('%d games.', true), $total_fields);
}
?></p>

<?php if (!empty($preview)): ?>
<p><?php
__('This will create the following games:');
echo $this->Html->nestedList($preview);
?></p>
<?php endif; ?>

<?php
if (empty($dates)):
?>
<p><?php
__('You have no future dates available.');
if (Configure::read('feature.allow_past_games')) {
	echo ' ';
	__('Choose "Schedule games in the past" below to see past options, or make future game slots available to this division and try again.');
}
?></p>
<?php
else:
	// We have an array like 0 => timestamp, and need timestamp => readable
	$dates = array_combine(array_values($dates), array_values($dates));
	if (empty($preview)) {
		$dates = array_map(array($this->ZuluruTime, 'fulldate'), $dates);
		echo $this->ZuluruForm->input('Game.start_date', array(
				'options' => $dates,
		));
	} else {
?>
<p><?php
__('Choose your preferred time slot for each round.');
echo ' ';
__('This allows you to ensure that teams have a maximum number of games on each day, place byes where necessary, etc.');
echo ' ';
__('Note that games will be placed no earlier than these time slots, but may be later depending on field availability.');
echo ' ';
__('Rounds may be scheduled to start after "later" rounds, for example if you have a particular matchup that you need to schedule at a particular time.');
echo ' ';
__('If you leave all rounds at the earliest possible time, the system will schedule games as closely as possible; you don\'t need to set each round\'s time if you have no constraints.');
echo ' ';
?></p>
<?php
		$dates = array_map(array($this->ZuluruTime, 'fulldatetime'), $dates);
		foreach (array_keys($preview) as $round) {
			echo $this->ZuluruForm->input("Game.start_date.$round", array(
					'label' => "Round $round",
					'options' => $dates,
			));
		}
	}
endif;
?>

</fieldset>

<?php
if (!empty($dates)) {
	echo $this->Form->end(__('Next step', true));
}
?>

<?php
if (Configure::read('feature.allow_past_games') && empty($this->data['Game']['past'])) {
	echo $this->Form->create ('Game', array('url' => Router::normalize($this->here)));
	echo $this->element('hidden', array('fields' => $saved_data));
	echo $this->Form->hidden('Game.past', array('value' => true));
	echo $this->Form->end(__('Schedule games in the past', true));
}
?>
</div>
