<?php
$this->Html->addCrumb (__('Registrations', true));
$this->Html->addCrumb (__('Unpaid', true));
?>

<div class="registrations index">
<h2><?php __('Unpaid Registrations');?></h2>

<?php
$rows = array();
$total = array('Unpaid' => 0, 'Pending' => 0);
$order_id_format = Configure::read('registration.order_id_format');
foreach ($registrations as $registration) {
	$order_id = sprintf($order_id_format, $registration['Registration']['id']);
	$rows[] = array(
		$this->Html->link($order_id, array('action' => 'view', 'registration' => $registration['Registration']['id'])),
		$this->Html->link($registration['Person']['full_name'], array('controller' => 'people', 'action' => 'view', 'person' => $registration['Person']['id'])),
		$this->ZuluruTime->datetime ($registration['Registration']['modified']),
		$registration['Registration']['payment'],
		array(
			$this->Html->link(__('Unregister', true), array('action' => 'unregister', 'registration' => $registration['Registration']['id']), array(),
					__('Are you sure you want to delete this registration?', true)) .
			$this->Html->link(__('Edit', true), array('action' => 'edit', 'registration' => $registration['Registration']['id'])),
			array('class' => 'actions'),
		)
	);
	$rows[] = array('', array($this->Html->link($registration['Event']['name'], array('controller' => 'events', 'action' => 'view', 'event' => $registration['Event']['id'])), array('colspan' => 5)));
	if (!empty ($registration['Registration']['notes'])) {
		$rows[] = array('', array($registration['Registration']['notes'], array('colspan' => 5)));
	}
	$rows[] = array(array('&nbsp;', array('colspan' => 5)));
	$total[$registration['Registration']['payment']] ++;
}

$total_rows = array();
foreach ($total as $key => $value) {
	$total_rows[] = array ($key, $value);
}

echo $this->Html->tag ('table', $this->Html->tableCells ($rows));
echo $this->Html->tag ('table', $this->Html->tableCells ($total_rows));
?>

</div>
