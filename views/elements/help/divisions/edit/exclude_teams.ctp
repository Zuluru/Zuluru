<p>If the "exclude teams" flag is set for the division, you will have the option, when adding games, to select teams that you don't want to include in the generated schedule.</p>
<p>You may want to do this because you have an un-even number of teams in your division, or if some teams may have bye weeks.</p>
<p>If you never need this option, <?php
if (array_key_exists ('division', $this->passedArgs)) {
	echo $this->Html->link(__('edit the division', true), array('controller' => 'divisions', 'action' => 'edit', 'division' => $this->passedArgs['division']));
} else {
	__('edit the division');
}
?> and turn it off.</p>
