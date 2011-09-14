<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb ($person['Person']['full_name']);
$this->Html->addCrumb (__('Team History', true));
?>

<div class="teams index">
<h2><?php echo __('Team History', true) . ': ' . $person['Person']['full_name'];?></h2>

<?php
$rows = array();
$last_year = null;
foreach ($teams as $team) {
	$year = date ('Y', strtotime ($team['League']['open']));
	if ($last_year != $year) {
		$last_year = $year;
	} else {
		$year = null;
	}
	$rows[] = array($year,
			$this->Html->link ($team['Team']['name'], array('controller' => 'teams', 'action' => 'view', 'team' => $team['Team']['id'])),
			$team['TeamsPerson']['position'],
			$this->Html->link ($team['League']['long_name'], array('controller' => 'leagues', 'action' => 'view', 'league' => $team['League']['id'])),
	);
}
echo $this->Html->tag ('table', $this->Html->tableCells ($rows), array('class' => 'list'));
?>

</div>
