<p>If the "double booking" flag is set for the division, you will have the option, when adding games or editing schedules, to put multiple games in the same game slot.</p>
<p>Most commonly, this is used by sports where multiple teams are competing at the same time with each getting an individual result unrelated to the scores of the other teams, such as a race.</p>
<p class="warning-message">Note that this disables sanity checks on the schedule, thereby allowing you to put as many games as you want on the same field at the same time, so you will need to double-check your schedules manually.</p>
<p>If you never need this option, <?php
if (array_key_exists ('division', $this->passedArgs)) {
	echo $this->Html->link(__('edit the division', true), array('controller' => 'divisions', 'action' => 'edit', 'division' => $this->passedArgs['division']));
} else {
	__('edit the division');
}
?> and turn it off.</p>
