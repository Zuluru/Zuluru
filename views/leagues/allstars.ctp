<?php
$this->Html->addCrumb (__('Leagues', true));
$this->Html->addCrumb ($league['League']['long_name']);
$this->Html->addCrumb (__('Allstar Nominations Report', true));
?>

<div class="leagues allstars">
<h2><?php  echo __('Allstar Nominations Report', true) . ': ' . $league['League']['long_name'];?></h2>

<?php
$rows = array();
$gender = null;
foreach ($allstars as $allstar) {
	if ($allstar['Person']['gender'] != $gender) {
		$gender = $allstar['Person']['gender'];
		$rows[] = array(array($this->Html->tag('h3', __($gender, true)), array('colspan' => 3)));
	}
	$rows[] = array(
		$this->Html->link ($allstar['Person']['full_name'], array('controller' => 'people', 'action' => 'view', 'person' => $allstar['Person']['id'])),
		$this->Html->link ($allstar['Person']['email'], "mailto:{$allstar['Person']['email']}"),
		$allstar[0]['count'],
	);
}
echo $this->Html->tag ('table', $this->Html->tableCells ($rows));
?>

</div>

<?php
if ($min > 1) {
	echo $this->Html->para(null, sprintf (__("This list shows only those with at least %d nominations. The %s is also available.", true),
			$min,
		$this->Html->link(__('complete list', true), array('action' => 'allstars', 'league' => $league['League']['id'], 'min' => 1))
	));
}
?>
