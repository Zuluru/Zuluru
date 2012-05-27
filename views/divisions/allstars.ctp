<?php
$this->Html->addCrumb (__('Divisions', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('Allstar Nominations Report', true));
?>

<div class="divisions allstars">
<h2><?php  echo __('Allstar Nominations Report', true) . ': ' . $division['Division']['full_league_name'];?></h2>

<?php
$rows = array();
$gender = null;
foreach ($allstars as $allstar) {
	if ($allstar['Person']['gender'] != $gender) {
		$gender = $allstar['Person']['gender'];
		$rows[] = array(array($this->Html->tag('h3', __($gender, true)), array('colspan' => 3)));
	}
	$rows[] = array(
		$this->element('people/block', array('person' => $allstar)),
		$this->Html->link ($allstar['Person']['email'], "mailto:{$allstar['Person']['email']}"),
		$allstar[0]['count'],
	);
}
echo $this->Html->tag ('table', $this->Html->tableCells ($rows), array('class' => 'list'));
?>

</div>

<?php
if ($min > 1) {
	echo $this->Html->para(null, sprintf (__("This list shows only those with at least %d nominations. The %s is also available.", true),
			$min,
		$this->Html->link(__('complete list', true), array('action' => 'allstars', 'division' => $division['Division']['id'], 'min' => 1))
	));
}
?>
