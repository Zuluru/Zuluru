<?php
$this->Html->addCrumb (__('League', true));
$this->Html->addCrumb ($league['League']['long_name']);
$this->Html->addCrumb (__('Add Games', true));
$this->Html->addCrumb (__('Select Exclusions', true));
?>

<div class="schedules add">
<p>The 'exclude teams' option is set for this league.
This gives you the chance to <b>EXCLUDE</b> some teams from scheduling.
You may want to do this because you have an un-even number of teams in your league,
or if your league consists of some teams who don't play every game...</p>
<p>Please select the teams you wish to <b>EXCLUDE</b> from scheduling.</p>
<p>You must ensure that you leave an even number of teams.</p>
<p>If you never need this option, <?php
echo $this->Html->link(__('edit the league', true), array('controller' => 'leagues', 'action' => 'edit', 'league' => $id));
?> and turn off this option.</p>
<?php
echo $this->Form->create ('Game', array('url' => array('controller' => 'schedules', 'action' => 'add', 'league' => $id)));
echo $this->Form->hidden('step', array('value' => 'exclude'));

foreach ($league['Team'] as $team) {
	echo $this->Form->input("ExcludeTeams.{$team['id']}", array(
			'label' => $team['name'],
			'type' => 'checkbox',
			'hiddenField' => false,
	));
}

echo $this->Form->end(__('Next step', true));
?>

</div>