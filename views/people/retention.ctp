<?php
$this->Html->addCrumb (__('People', true));
$this->Html->addCrumb (__('Membership Retention Statistics', true));
?>

<div class="people retention">
<h2><?php echo __('Membership Retention Statistics', true);?></h2>

<?php
if (!isset($past_events)):
	echo $this->Form->create(false, array('url' => Router::normalize($this->here)));
	$years = array_combine(range(date('Y'), $min), range(date('Y'), $min));
	echo $this->Form->input('start', array(
			'label' => __('Include details starting in', true),
			'options' => $years,
	));
	echo $this->Form->input('end', array(
			'label' => __('Up to and including', true),
			'options' => $years,
	));
	echo $this->Form->input('download', array(
			'type' => 'checkbox',
	));
	echo $this->Form->end(__('Submit', true));
else:
?>

<?php
// The list of events across the top should not include the first one, as it's an empty column
$short_event_list = $event_list;
array_shift($short_event_list);
?>
<table class="list">
	<tr>
		<th><?php __('Membership Registration'); ?></th>
		<?php foreach ($short_event_list as $event): ?>
		<th><?php echo $this->Html->link ($event['Event']['name'], array('controller' => 'events', 'action' => 'view', 'event' => $event['Event']['id'])); ?></th>
		<?php endforeach; ?>
	</tr>
<?php
$i = 0;
foreach ($past_events as $past_id => $counts):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
	$event = array_shift(Set::extract("/Event[id=$past_id]/name", $event_list));
?>
	<tr<?php echo $class;?>>
		<td><?php echo $this->Html->link ($event, array('controller' => 'events', 'action' => 'view', 'event' => $past_id)); ?></td>
		<?php foreach ($short_event_list as $event): ?>
		<td><?php
		if (array_key_exists($event['Event']['id'], $counts)) {
			echo $counts[$event['Event']['id']];
		}
		?></td>
		<?php endforeach; ?>
	</tr>
<?php endforeach; ?>
<?php
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<th><?php __('Total Prior'); ?></th>
		<?php foreach ($short_event_list as $event): ?>
		<th><?php echo $event['total']; ?></th>
		<?php endforeach; ?>
	</tr>
<?php
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<th><?php __('Total Registered'); ?></th>
		<?php foreach ($short_event_list as $event): ?>
		<th><?php echo $event['count']; ?></th>
		<?php endforeach; ?>
	</tr>
<?php
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<th><?php __('% Prior'); ?></th>
		<?php foreach ($short_event_list as $event): ?>
		<th><?php printf('%2.1f', $event['total'] * 100 / $event['count']); ?></th>
		<?php endforeach; ?>
	</tr>
</table>
<?php
endif;
?>
</div>
