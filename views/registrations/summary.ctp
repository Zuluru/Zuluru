<?php
$this->Html->addCrumb (__('Registrations', true));
$this->Html->addCrumb ($event['Event']['name']);
$this->Html->addCrumb (__('Summary', true));
?>

<div class="registrations summary">
<h2><?php echo __('Registration Summary', true) . ': ' . $event['Event']['name'];?></h2>

<?php
$rows = array();

$title = __('By gender:', true);
foreach ($gender as $value) {
	$rows[] = array(
		$title,
		$value['Person']['gender'],
		$value[0]['count'],
	);
	$title = '';
}

$title = __('By payment:', true);
foreach ($payment as $value) {
	$rows[] = array(
		$title,
		$value['Registration']['payment'],
		$value[0]['count'],
	);
	$title = '';
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
