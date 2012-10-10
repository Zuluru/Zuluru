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

	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $this->Html->link ($registration['Event']['name'], array('controller' => 'events', 'action' => 'view', 'event' => $registration['Event']['id'])); ?>
		</td>
		<td>
			<?php
			$order = sprintf (Configure::read('registration.order_id_format'), $registration['Registration']['id']);
			if ($is_admin) {
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
			if ($is_admin) {
				echo $this->Html->link (__('View', true), array('controller' => 'registrations', 'action' => 'view', 'registration' => $registration['Registration']['id']));
				echo $this->Html->link(__('Edit', true), array('controller' => 'registrations', 'action' => 'edit', 'registration' => $registration['Registration']['id']));
			}
			echo $this->Html->link(__('Unregister', true), array('controller' => 'registrations', 'action' => 'unregister', 'registration' => $registration['Registration']['id'], 'return' => true), null, sprintf(__('Are you sure you want to delete # %s?', true), $registration['Registration']['id']));
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
