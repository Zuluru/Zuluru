<?php
$this->Html->addCrumb (__('Preregistrations', true));
$this->Html->addCrumb (__('List', true));
if (isset($event)) {
	if (count($affiliates) > 1) {
		$this->Html->addCrumb ($event['Affiliate']['name']);
	}
	$this->Html->addCrumb ($event['Event']['name']);
}
?>

<div class="preregistrations index">
	<h2><?php
	__('Preregistrations');
	if (isset($event)) {
		echo ': ';
		if (count($affiliates) > 1) {
			echo "{$event['Affiliate']['name']} ";
		}
		echo $event['Event']['name'];
	}
	?></h2>
	<table class="list">
	<tr>
			<th><?php echo $this->Paginator->sort('person_id');?></th>
			<?php if (!isset($event)): ?>
			<th><?php echo $this->Paginator->sort('event_id');?></th>
			<?php endif; ?>
			<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
	$i = 0;
	$affiliate_id = null;
	foreach ($preregistrations as $preregistration):
		if (!isset($event) && count($affiliates) > 1 && $preregistration['Event']['affiliate_id'] != $affiliate_id):
			$affiliate_id = $preregistration['Event']['affiliate_id'];
	?>
	<tr>
		<th colspan="<?php echo 2 + (!isset($event)); ?>">
			<h3 class="affiliate"><?php echo $preregistration['Event']['Affiliate']['name']; ?></h3>
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
			<?php echo $this->element('people/block', array('person' => $preregistration['Person'])); ?>
		</td>
		<?php if (!isset($event)): ?>
		<td>
			<?php echo $this->Html->link($preregistration['Event']['name'], array('controller' => 'events', 'action' => 'view', 'event' => $preregistration['Event']['id'])); ?>
		</td>
		<?php endif; ?>
		<td class="actions">
			<?php echo $this->Html->link(__('Delete', true), array('action' => 'delete', 'prereg' => $preregistration['Preregistration']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $preregistration['Preregistration']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
	));
	?>	</p>

	<div class="paging">
		<?php echo $this->Paginator->prev('<< ' . __('previous', true), array(), null, array('class'=>'disabled'));?>
	 | 	<?php echo $this->Paginator->numbers();?>
 |
		<?php echo $this->Paginator->next(__('next', true) . ' >>', array(), null, array('class' => 'disabled'));?>
	</div>
</div>
<div class="actions">
	<ul>
		<li><?php
		$url = array('action' => 'add');
		if (isset($event)) {
			$url['event'] = $event['Event']['id'];
		}
		echo $this->Html->link(__('New Preregistration', true), $url);
		?></li>
	</ul>
</div>