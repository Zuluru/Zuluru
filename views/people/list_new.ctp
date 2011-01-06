<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb (__('New Accounts', true));
?>

<div class="people list_new">
<h2><?php __('New Accounts');?></h2>

<?php
$rows = array();
foreach ($new as $player) {
	$links = array(
		$this->Html->link (__('View', true), array('action' => 'view', 'person' => $player['Person']['id'])),
		$this->Html->link (__('Approve', true), array('action' => 'approve', 'person' => $player['Person']['id'])),
		$this->Html->link (__('Delete', true), array('action' => 'delete', 'person' => $player['Person']['id'])),
		$this->Html->link (__('Edit', true), array('action' => 'edit', 'person' => $player['Person']['id'])),
	);
	// TODO: flag potential duplicates
	$rows[] = array($player['Person']['full_name'], array(implode ('', $links), array('class' => 'actions')));
}
echo $this->Html->tag('table', $this->Html->tableCells ($rows, array(), array('class' => 'altrow')));
?>

</div>
