<?php
$this->Html->addCrumb (__('Registrations', true));
$this->Html->addCrumb (__('Statistics', true));
?>

<div class="registrations statistics">
<h2><?php __('Registration Statistics');?></h2>

<?php
$rows = array();
$group = $affiliate_id = null;
foreach ($events as $event) {
	if (count($affiliates) > 1 && $event['Event']['affiliate_id'] != $affiliate_id) {
		$affiliate_id = $event['Event']['affiliate_id'];
		$rows[] = array(array($this->Html->tag('h3', $event['Affiliate']['name']), array('class' => 'affiliate', 'colspan' => 2)));
	}

	if ($event['EventType']['name'] != $group) {
		$group = $event['EventType']['name'];
		$rows[] = array(array($this->Html->tag('h4', $group), array('colspan' => 2)));
	}
	$rows[] = array($this->Html->link($event['Event']['name'], array('action' => 'summary', 'event' => $event['Event']['id'])),
		$event[0]['count']);
}

echo $this->Html->tag('table', $this->Html->tableCells ($rows, array(), array('class' => 'altrow')), array('class' => 'list'));
?>

</div>
<div class="actions">
	<ul>
<?php
foreach ($years as $year) {
	echo $this->Html->tag('li', $this->Html->link($year[0]['year'], array('action' => 'statistics', 'year' => $year[0]['year'])));
}
?>

	</ul>
</div>
