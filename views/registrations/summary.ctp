<?php
$this->Html->addCrumb (__('Registrations', true));
$this->Html->addCrumb ($event['Event']['name']);
$this->Html->addCrumb (__('Summary', true));
?>

<div class="registrations summary">
<h2><?php echo __('Registration Summary', true) . ': ' . $event['Event']['name'];?></h2>

<?php
$rows = array();

if (isset($gender)) {
	$title = __('By gender:', true);
	foreach ($gender as $value) {
		$rows[] = array(
			array($title, array('colspan' => 2)),
			$value['Person']['gender'],
			$value[0]['count'],
		);
		$title = '';
	}
}

$title = __('By payment:', true);
$last_payment = null;
$total = 0;
foreach ($payment as $value) {
	if ($last_payment == $value['Registration']['payment']) {
		$row = array(
			$title,
			null,
		);
	} else {
		if ($total != 0 && isset($gender)) {
			$rows[] = array(
				null,
				null,
				__('Total', true),
				$total,
			);
			$total = 0;
		}

		$row = array(
			$title,
			$value['Registration']['payment'],
		);
		$last_payment = $value['Registration']['payment'];
	}

	if (isset($gender)) {
		$row[] = $value['Person']['gender'];
	}
	$row[] = $value[0]['count'];

	$rows[] = $row;
	$total += $value[0]['count'];
	$title = '';
}

if ($total != 0 && isset($gender)) {
	$rows[] = array(
		null,
		null,
		__('Total', true),
		$total,
	);
	$total = 0;
}

echo $this->Html->tag ('table', $this->Html->tableCells ($rows, array(), array('class' => 'altrow')), array('class' => 'list'));
echo $this->element('questionnaires/summary');
?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(sprintf(__('View %s', true), __('Event', true)), array('controller' => 'events', 'action' => 'view', 'event' => $event['Event']['id'])); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('Detailed %s List', true), __('Registration', true)), array('action' => 'full_list', 'event' => $event['Event']['id'])); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('Download %s List', true), __('Registration', true)), array('action' => 'full_list', 'event' => $event['Event']['id'], 'ext' => 'csv')); ?> </li>
	</ul>
</div>
