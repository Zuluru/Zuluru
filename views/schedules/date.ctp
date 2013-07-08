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
