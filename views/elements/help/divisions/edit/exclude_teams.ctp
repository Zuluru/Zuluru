<p><?php __('If the "exclude teams" flag is set for the division, you will have the option, when adding games, to select teams that you don\'t want to include in the generated schedule.'); ?></p>
<p><?php __('You may want to do this because you have an un-even number of teams in your division, or if some teams may have bye weeks.'); ?></p>
<p><?php
if (array_key_exists ('division', $this->passedArgs)) {
	$edit = $this->Html->link(__('edit the division', true), array('controller' => 'divisions', 'action' => 'edit', 'division' => $this->passedArgs['division']));
} else {
	$edit = __('edit the division', true);
}
printf(__('If you never need this option, %s and turn it off.', true), $edit);
?></p>
