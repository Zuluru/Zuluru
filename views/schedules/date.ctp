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

// We won't bother validating the start date.  There's no way to select
// "no date" on the form, so any submission would be valid, and we trust
// our limited number of coordinators not to be hacking the system to
// try to schedule games on dates that aren't available.  Besides, the
// algorithm won't run if game slots aren't available, so there's no real
// harm that can be done even if they did hack it.  As a result, we don't
// direct this form's data back to the 'date' step, we go straight to
// confirmation.
$this->data['Game']['step'] = 'confirm';
echo $this->element('hidden', array('fields' => $this->data));
?>

<fieldset>
<legend>Select desired start date</legend>

<p>Scheduling a <?php __($desc); ?> will require <?php echo $num_fields * $num_dates; ?> fields:
<?php echo $num_fields; ?> per day on <?php echo $num_dates; ?> dates.</p>

<?php
// We have an array like 0 => timestamp, and need timestamp => readable
$dates = array_combine(array_values($dates), array_values($dates));
$dates = array_map(array($this->ZuluruTime, 'fulldate'), $dates);
echo $this->Form->input('start_date', array(
		'options' => $dates,
));
?>

</fieldset>

<?php echo $this->Form->end(__('Next step', true)); ?>
</div>
