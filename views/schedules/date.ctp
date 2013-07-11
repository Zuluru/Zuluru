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
$this->data['Game']['step'] = 'date';
echo $this->element('hidden', array('fields' => $this->data));
?>

<fieldset>
<legend>Select desired start date</legend>

<p><?php
printf (__('Scheduling a %s will create a total of ', true), __($desc, true));
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
<p>This will create the following games:<?php echo $this->Html->nestedList($preview); ?></p>
<?php endif; ?>

<?php
// We have an array like 0 => timestamp, and need timestamp => readable
$dates = array_combine(array_values($dates), array_values($dates));
if (empty($preview)) {
	$dates = array_map(array($this->ZuluruTime, 'fulldate'), $dates);
	echo $this->ZuluruForm->input('Game.start_date', array(
			'options' => $dates,
	));
} else {
?>
<p>Choose your preferred time slot for each round.
This allows you to ensure that teams have a maximum number of games on each day, place byes where necessary, etc.
Note that games will be placed no earlier than these time slots, but may be later depending on field availability.
Rounds may be scheduled to start after "later" rounds, for example if you have a particular matchup that you need to schedule at a particular time.
If you leave all rounds at the earliest possible time, the system will schedule games as closely as possible; you don't need to set each round's time if you have no constraints.</p>
<?php
	$dates = array_map(array($this->ZuluruTime, 'fulldatetime'), $dates);
	foreach (array_keys($preview) as $round) {
		echo $this->ZuluruForm->input("Game.start_date.$round", array(
				'label' => "Round $round",
				'options' => $dates,
		));
	}
}
?>

</fieldset>

<?php echo $this->Form->end(__('Next step', true)); ?>
</div>
