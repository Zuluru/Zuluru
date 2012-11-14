<?php if (!$this->params['isAjax']): ?>

<?php
$this->Html->addCrumb (__('Registrations', true));
$this->Html->addCrumb ($event['Event']['name']);
$this->Html->addCrumb (__('List', true));
?>

<div class="registrations index">
<h2><?php echo __('Registration List', true) . ': ' . $event['Event']['name'];?></h2>

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
<table class="list">
<tr>
	<th><?php echo $this->Paginator->sort('Order ID', 'id', array('buffer' => false));?></th>
	<th><?php __('Person'); ?></th>
	<th><?php echo $this->Paginator->sort('Date', 'created', array('buffer' => false));?></th>
	<th><?php echo $this->Paginator->sort('payment', null, array('buffer' => false));?></th>
</tr>
<?php
$i = 0;
foreach ($registrations as $registration):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td>
			<?php
			$order = sprintf (Configure::read('registration.order_id_format'), $registration['Registration']['id']);
			echo $this->Html->link ($order, array('controller' => 'registrations', 'action' => 'view', 'registration' => $registration['Registration']['id']));
			?>
		</td>
		<td>
			<?php echo $this->element('people/block', array('person' => $registration['Person'])); ?>
		</td>
		<td>
			<?php echo $registration['Registration']['created']; ?>
		</td>
		<td>
			<?php echo $registration['Registration']['payment']; ?>
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

<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(sprintf(__('View %s', true), __('Event', true)), array('controller' => 'events', 'action' => 'view', 'event' => $event['Event']['id'])); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('%s Summary', true), __('Registration', true)), array('action' => 'summary', 'event' => $event['Event']['id'])); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('Download %s List', true), __('Registration', true)), array('action' => 'full_list', 'event' => $event['Event']['id'], 'ext' => 'csv')); ?> </li>
	</ul>
</div>

</div>
<?php endif; ?>
