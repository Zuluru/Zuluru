<p>If the "exclude teams" flag is set for the league, you will have the option, when adding games, to select teams that you don't want to include in the generated schedule.</p>
<p>You may want to do this because you have an un-even number of teams in your league, or if your league consists of some teams who don't play every game.</p>
<p>If you never need this option, <?php
if (array_key_exists ('league', $this->passedArgs)) {
	echo $this->Html->link(__('edit the league', true), array('controller' => 'leagues', 'action' => 'edit', 'league' => $this->passedArgs['league']));
} else {
	__('edit the league');
}
?> and turn off this option.</p>
