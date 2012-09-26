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
		$this->Html->link (__('Edit', true), array('action' => 'edit', 'person' => $player['Person']['id'], 'return' => true)),
		$this->Html->link (__('Approve', true), array('action' => 'approve', 'person' => $player['Person']['id'])),
	);
	if (Configure::read('feature.manage_accounts')) {
		$links[] = $this->Html->link (__('Delete', true), array('action' => 'delete', 'person' => $player['Person']['id'], 'return' => true));
	}

	$class = ($player['Person']['duplicate'] ? 'warning-message' : '');
	$rows[] = array(
		array($player['Person']['full_name'], array('class' => $class)),
		array(implode ('', $links), array('class' => 'actions'))
	);
}
if (empty($rows)) {
	echo $this->Html->para(null, __('No accounts to approve.', true));
} else {
	echo $this->Html->tag('table', $this->Html->tableCells ($rows, array(), array('class' => 'altrow')), array('class' => 'list'));
}
?>

</div>
