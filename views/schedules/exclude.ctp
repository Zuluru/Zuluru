<?php
$this->Html->addCrumb (__('League', true));
$this->Html->addCrumb ($league['League']['long_name']);
$this->Html->addCrumb (__('Add Games', true));
$this->Html->addCrumb (__('Select Exclusions', true));
?>

<div class="schedules add">
<p>The 'exclude teams' option is set for this league. <?php echo $this->ZuluruHtml->help(array('action' => 'leagues', 'edit', 'exclude_teams')); ?></p>
<p>Please select the teams you wish to <b>EXCLUDE</b> from scheduling. You must ensure that you leave an even number of teams.</p>
<?php
echo $this->Form->create ('Game', array('url' => Router::normalize($this->here)));
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