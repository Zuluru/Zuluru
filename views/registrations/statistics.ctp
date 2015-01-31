<?php
$this->Html->addCrumb (__('Registrations', true));
$this->Html->addCrumb (__('Statistics', true));
?>

<div class="registrations statistics">
<h2><?php __('Registration Statistics');?></h2>

<?php
$types = Set::extract('/EventType/name/..', $events);
$types = Set::combine($events, '{n}.EventType.id', '{n}.EventType.name');
ksort($types);
echo $this->element('selector', array('title' => 'Type', 'options' => $types));

$sports = array_unique(Set::extract('/League/sport', $events));
echo $this->element('selector', array('title' => 'Sport', 'options' => $sports));

$seasons = array_unique(Set::extract('/League/season', $events));
echo $this->element('selector', array('title' => 'Season', 'options' => $seasons));

$days = Set::extract('/Day[id!=]', $events);
$days = Set::combine($days, '{n}.Day.id', '{n}.Day.name');
ksort($days);
echo $this->element('selector', array('title' => 'Day', 'options' => $days));

$play_types = array('team', 'individual');
?>

<table class="list">
<?php
$group = $affiliate_id = null;
foreach ($events as $event):
	if (count($affiliates) > 1 && $event['Event']['affiliate_id'] != $affiliate_id):
		$affiliate_id = $event['Event']['affiliate_id'];
?>
<tr><td colspan="2" class="affiliate"><h3><?php echo $event['Affiliate']['name']; ?></h3></td></tr>
<?php
	endif;

	if ($event['EventType']['name'] != $group):
		$group = $event['EventType']['name'];

		$classes = array();
		$classes[] = $this->element('selector_classes', array('title' => 'Type', 'options' => $event['EventType']['name']));
		if (in_array($event['EventType']['type'], $play_types)) {
			$divisions = Set::extract("/Event[event_type_id={$event['Event']['event_type_id']}]/..", $events);

			$sports = array_unique(Set::extract('/League/sport', $divisions));
			$classes[] = $this->element('selector_classes', array('title' => 'Sport', 'options' => $sports));

			$seasons = array_unique(Set::extract('/League/season', $divisions));
			$classes[] = $this->element('selector_classes', array('title' => 'Season', 'options' => $seasons));

			$days = Set::extract('/Day[id!=]', $divisions);
			$days = Set::combine($days, '{n}.Day.id', '{n}.Day.name');
			ksort($days);
			$classes[] = $this->element('selector_classes', array('title' => 'Day', 'options' => $days));
		}
?>
<tr class="<?php echo implode(' ', $classes); ?>"><td colspan="2"><h4><?php echo $group; ?></h4></td></tr>
<?php
	endif;

	$classes = array();
	$classes[] = $this->element('selector_classes', array('title' => 'Type', 'options' => $event['EventType']['name']));
	if (in_array($event['EventType']['type'], $play_types) && !empty($event['Division']['id'])) {
		$classes[] = $this->element('selector_classes', array('title' => 'Sport', 'options' => $event['League']['sport']));
		$classes[] = $this->element('selector_classes', array('title' => 'Season', 'options' => $event['League']['season']));
		$days = Set::combine($event, 'Day.{n}.id', 'Day.{n}.name');
		ksort($days);
		$classes[] = $this->element('selector_classes', array('title' => 'Day', 'options' => $days));
	} else {
		$classes[] = $this->element('selector_classes', array('title' => 'Sport', 'options' => array()));
		$classes[] = $this->element('selector_classes', array('title' => 'Season', 'options' => array()));
		$classes[] = $this->element('selector_classes', array('title' => 'Day', 'options' => array()));
	}
?>

<tr class="<?php echo implode(' ', $classes); ?>">
	<td><?php echo $this->Html->link($event['Event']['name'], array('action' => 'summary', 'event' => $event['Event']['id'])); ?></td>
	<td><?php echo $event[0]['count']; ?></td>
</tr>
<?php endforeach; ?>

</table>
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
