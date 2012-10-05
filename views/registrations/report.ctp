<?php if (!$this->params['isAjax']): ?>

<?php
$this->Html->addCrumb (__('Registrations', true));
$this->Html->addCrumb (__('Report', true));
?>

<div class="registrations index">
<h2><?php echo __('Registration Report', true);?></h2>

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
	<th><?php echo $this->Paginator->sort(__('Created Date', true), 'Registration.created', array('buffer' => false));?></th>
	<th><?php echo $this->Paginator->sort(__('Order ID', true), 'Registration.id', array('buffer' => false));?></th>
	<th><?php echo $this->Paginator->sort(__('Event ID', true), 'Event.id', array('buffer' => false));?></th>
	<th><?php __('Event');?></th>
	<th><?php echo $this->Paginator->sort(__('User ID', true), 'Person.id', array('buffer' => false));?></th>
	<th><?php __('First Name');?></th>
	<th><?php __('Last Name');?></th>
	<th><?php __('Payment');?></th>
	<th><?php __('Amount');?></th>
</tr>
<?php
$order_fmt = Configure::read('registration.order_id_format');
$i = 0;
$affiliate_id = null;
foreach ($registrations as $registration):
	if (count($affiliates) > 1 && $registration['Event']['affiliate_id'] != $affiliate_id):
		$affiliate_id = $registration['Event']['affiliate_id'];
?>
<tr>
	<th colspan="9">
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
			<?php echo $registration['Registration']['created']; ?>
		</td>
		<td>
			<?php echo $this->Html->link (sprintf ($order_fmt, $registration['Registration']['id']),
				array('controller' => 'registrations', 'action' => 'view', 'registration' => $registration['Registration']['id'])); ?>
		</td>
		<td>
			<?php echo $registration['Event']['id']; ?>
		</td>
		<td>
			<?php echo $registration['Event']['name']; ?>
		</td>
		<td>
			<?php echo $registration['Person']['id']; ?>
		</td>
		<td>
			<?php echo $this->element('people/block', array('person' => $registration['Person'], 'display_field' => 'first_name')); ?>
		</td>
		<td>
			<?php echo $this->element('people/block', array('person' => $registration['Person'], 'display_field' => 'last_name')); ?>
		</td>
		<td>
			<?php echo $registration['Registration']['payment']; ?>
		</td>
		<td>
			<?php echo $registration['Event']['cost'] + $registration['Event']['tax1'] + $registration['Event']['tax2']; ?>
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

<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(sprintf(__('Download %s Report', true), __('Registration', true)), array('action' => 'report', 'affiliate' => $affiliate, 'ext' => 'csv')); ?> </li>
	</ul>
</div>

<?php endif; ?>
