<?php
$this->Html->addCrumb (__('League', true));
$this->Html->addCrumb ($league['League']['long_name']);
$this->Html->addCrumb (__('Add Games', true));
$this->Html->addCrumb (__('Select Date', true));
?>

<div class="schedules add">
<?php echo $this->element('schedule/exclude'); ?>

<?php
echo $this->Form->create ('Game', array('url' => array('controller' => 'schedules', 'action' => 'add', 'league' => $id)));
$this->data['Game']['step'] = 'date';
echo $this->element('hidden', array('fields' => $this->data));
?>

<fieldset>
<legend>Select desired start date</legend>

<p><?php
printf (__('Scheduling a %s will create a total of ', true), __($desc, true));
if (is_array(current($num_fields))) {
	// num_fields will be a multi-dimensional array if we're scheduling multiple blocks of games.
	$total_fields = array_sum(array_map('array_sum', $num_fields));
	$min_slots = max(array_map('count', $num_fields));
	printf (__('%d games across a minimum of %d time slots.', true), $total_fields, $min_slots);
	$slots = array();
	foreach ($this->data['Game']['name'] as $key => $name) {
		$slots[] = $name . ': ' . implode(', ', $num_fields[$key]);
	}
	__(' Games per time slot for each pool are as follows:');
	echo $this->Html->nestedList($slots);
} else if (count($num_fields) > 1) {
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

<?php
// We have an array like 0 => timestamp, and need timestamp => readable
$dates = array_combine(array_values($dates), array_values($dates));
$dates = array_map(array($this->ZuluruTime, 'fulldate'), $dates);
echo $this->ZuluruForm->input('start_date', array(
		'options' => $dates,
));
?>

</fieldset>

<?php echo $this->Form->end(__('Next step', true)); ?>
</div>
