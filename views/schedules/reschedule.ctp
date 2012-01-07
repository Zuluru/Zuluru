<?php
$this->Html->addCrumb (__('Division', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('Reschedule Day', true));
?>

<div class="schedules reschedule">
<h2><?php __('Schedule Reschedule');?></h2>

<p><?php printf (__('You are about to reschedule %d games originally scheduled for %s.', true),
	count($division['Game']), $this->ZuluruTime->fulldate ($date)); ?></p>

<?php
echo $this->Form->create (false, array('url' => Router::normalize($this->here)));

// We have an array like 0 => timestamp, and need timestamp => readable
$dates = array_combine(array_values($dates), array_values($dates));
$dates = array_map(array($this->ZuluruTime, 'fulldate'), $dates);
echo $this->Form->input('new_date', array(
		'label' => __('Reschedule games to', true),
		'options' => $dates,
));

echo $this->Form->input('publish', array(
		'label' => __('Publish rescheduled games for player viewing?', true),
		'type' => 'checkbox',
));

echo $this->Form->end(__('Continue', true));
$confirm = __('Are you sure you want to reschedule these games? This cannot be undone.', true);
$this->Js->get(':submit')->event('click', "return confirm('$confirm');");
?>

</div>
