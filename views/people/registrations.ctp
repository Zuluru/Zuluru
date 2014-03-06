<?php if (!$this->params['isAjax']): ?>

<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb ($person['Person']['full_name']);
$this->Html->addCrumb (__('Registration History', true));
?>

<div class="people registrations">
<h2><?php echo __('Registration History', true) . ': ' . $person['Person']['full_name'];?></h2>

<div id="RegistrationList">

<?php endif; ?>

<div class="index">
<p>
<?php
// TODO: Test when JS is disabled
$this->Paginator->options(array(
	'update' => '#RegistrationList',
	'evalScripts' => true,
));
echo $this->Paginator->counter(array(
'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
));
?></p>

<?php
$types = Set::extract('/Event/EventType/name/..', $registrations);
$types = Set::combine($types, '{n}.EventType.id', '{n}.EventType.name');
ksort($types);
echo $this->element('selector', array('title' => 'Type', 'options' => $types));

$seasons = array_unique(Set::extract('/Event/Division/League/season', $registrations));
echo $this->element('selector', array('title' => 'Season', 'options' => array_intersect(array_keys(Configure::read('options.season')), $seasons)));

$days = Set::extract('/Event/Division/Day[id!=]', $registrations);
$days = Set::combine($days, '{n}.Day.id', '{n}.Day.name');
ksort($days);
echo $this->element('selector', array('title' => 'Day', 'options' => $days));

$play_types = array('team', 'individual');
?>

<table class="list">
<tr>
	<th><?php echo $this->Paginator->sort('Event Name', 'Event.name', array('buffer' => false));?></th>
	<th><?php echo $this->Paginator->sort('Order ID', 'id', array('buffer' => false));?></th>
	<th><?php echo $this->Paginator->sort('Date', 'created', array('buffer' => false));?></th>
	<th><?php echo $this->Paginator->sort('payment', null, array('buffer' => false));?></th>
	<th><?php __('Actions');?></th>
</tr>
<?php
$i = 0;
$affiliate_id = null;
foreach ($registrations as $registration):
	if (count($affiliates) > 1 && $registration['Event']['affiliate_id'] != $affiliate_id):
		$affiliate_id = $registration['Event']['affiliate_id'];
?>
<tr>
	<th colspan="5">
		<h3 class="affiliate"><?php echo $registration['Event']['Affiliate']['name']; ?></h3>
	</th>
</tr>
<?php
	endif;

	$classes = array();
	if ($i++ % 2 == 0) {
		$classes[] = 'altrow';
	}
	$classes[] = $this->element('selector_classes', array('title' => 'Type', 'options' => $registration['Event']['EventType']['name']));
	if (in_array($registration['Event']['EventType']['type'], $play_types) && !empty($registration['Event']['Division']['id'])) {
		$classes[] = $this->element('selector_classes', array('title' => 'Season', 'options' => $registration['Event']['Division']['League']['season']));
		$days = Set::combine($registration['Event'], 'Division.Day.{n}.id', 'Division.Day.{n}.name');
		ksort($days);
		$classes[] = $this->element('selector_classes', array('title' => 'Day', 'options' => $days));
	} else {
		$classes[] = $this->element('selector_classes', array('title' => 'Season', 'options' => array()));
		$classes[] = $this->element('selector_classes', array('title' => 'Day', 'options' => array()));
	}
	$class = ' class="' . implode(' ', $classes) . '"';
?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $this->Html->link ($registration['Event']['name'], array('controller' => 'events', 'action' => 'view', 'event' => $registration['Event']['id'])); ?>
		</td>
		<td>
			<?php
			$order = sprintf (Configure::read('registration.order_id_format'), $registration['Registration']['id']);
			if ($is_admin || $is_manager) {
				echo $this->Html->link ($order, array('controller' => 'registrations', 'action' => 'view', 'registration' => $registration['Registration']['id']));
			} else {
				echo $order;
			}
			?>
		</td>
		<td>
			<?php echo $registration['Registration']['created']; ?>
		</td>
		<td>
			<?php echo $registration['Registration']['payment']; ?>
		</td>
		<td class="actions">
			<?php
			if ($is_admin || $is_manager) {
				echo $this->Html->link (__('View', true), array('controller' => 'registrations', 'action' => 'view', 'registration' => $registration['Registration']['id']));
				echo $this->Html->link(__('Edit', true), array('controller' => 'registrations', 'action' => 'edit', 'registration' => $registration['Registration']['id']));
			}
			if (in_array($registration['Registration']['payment'], Configure::read('registration_none_paid')) || $registration['Registration']['total_amount'] == 0) {
				if (!$is_admin && !$is_manager) {
					echo $this->Html->link(__('Edit', true), array('controller' => 'registrations', 'action' => 'edit', 'registration' => $registration['Registration']['id'], 'return' => true));
				}
				echo $this->Html->link(__('Unregister', true), array('controller' => 'registrations', 'action' => 'unregister', 'registration' => $registration['Registration']['id'], 'return' => true), null, sprintf(__('Are you sure you want to delete # %s?', true), $registration['Registration']['id']));
			}
			?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
</div>
<div class="paging">
	<?php echo $this->Paginator->prev('<< '.__('previous', true), array('buffer' => false), null, array('class'=>'disabled'));?>
 | 	<?php echo $this->Paginator->numbers(array('buffer' => false));?> | 
	<?php echo $this->Paginator->next(__('next', true).' >>', array('buffer' => false), null, array('class' => 'disabled'));?>
</div>

<?php if (!$this->params['isAjax']): ?>

</div>
</div>

<?php endif; ?>
