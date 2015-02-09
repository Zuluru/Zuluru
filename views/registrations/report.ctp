<?php if (!$this->params['isAjax']): ?>

<?php
$this->Html->addCrumb (__('Registrations', true));
$this->Html->addCrumb (__('Report', true));
?>

<div class="registrations index">
<h2><?php echo __('Registration Report', true);?></h2>

<div id="RegistrationList">

<?php endif; ?>

<?php
if (!isset($registrations)):
	echo $this->Form->create('Registration', array('url' => Router::normalize($this->here)));
?>

	<fieldset>
		<legend><?php __('Date Range'); ?></legend>
	<?php
		// In January and February, default report range to last year
		$adjust = (date('n') <= 2 ? ' -1 year' : '');
		echo $this->ZuluruForm->input('start_date', array(
				'type' => 'date',
				'value' => date('Y-m-d', strtotime("Jan 1$adjust")),
				'maxYear' => date('Y'),
		));
		echo $this->ZuluruForm->input('end_date', array(
				'type' => 'date',
				'value' => date('Y-m-d', strtotime("Dec 31$adjust")),
				'maxYear' => date('Y'),
		));
	?>
	</fieldset>
<?php
	echo $this->Form->end(__('Submit', true));
	echo $this->ZuluruHtml->script ('datepicker.js', array('inline' => false));
else:
?>
<div class="index">
<p>
<?php
$this->Paginator->options(array(
	'update' => '#RegistrationList',
	'evalScripts' => true,
	'url' => compact('start_date', 'end_date'),
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
	<th><?php __('Price Point'); ?></th>
	<th><?php echo $this->Paginator->sort(__('User ID', true), 'Person.id', array('buffer' => false));?></th>
	<th><?php __('First Name');?></th>
	<th><?php __('Last Name');?></th>
	<th><?php __('Payment');?></th>
	<th><?php __('Total Amount');?></th>
	<th><?php __('Amount Paid');?></th>
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
			<?php echo $registration['Price']['name']; ?>
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
			<?php echo $registration['Registration']['total_amount']; ?>
		</td>
		<td>
			<?php echo array_sum(Set::extract('/Payment/payment_amount', $registration)); ?>
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

<?php endif; ?>

<?php if (!$this->params['isAjax']): ?>

</div>
</div>

<?php if (!empty($this->data)): ?>

	<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(sprintf(__('Download %s Report', true), __('Registration', true)), array('action' => 'report', 'affiliate' => $affiliate, 'start_date' => $start_date, 'end_date' => $end_date, 'ext' => 'csv')); ?> </li>
	</ul>
</div>

<?php endif; ?>

<?php endif; ?>
