<?php
$this->Html->addCrumb (__('Questionnaire', true));
$this->Html->addCrumb ($questionnaire['Questionnaire']['name']);
$this->Html->addCrumb (__('View', true));
?>

<div class="questionnaires view">
<h2><?php echo $questionnaire['Questionnaire']['name'];?></h2>
	<?php if (count($affiliates) > 1): ?>
	<dl><?php $i = 1; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Affiliate'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($questionnaire['Affiliate']['name'], array('controller' => 'affiliates', 'action' => 'view', 'affiliate' => $questionnaire['Affiliate']['id'])); ?>

		</dd>
	</dl>
	<?php endif; ?>
<?php echo $this->Form->create (false, array('url' => Router::normalize($this->here))); ?>
	<fieldset><legend><?php __('Questionnaire Preview'); ?></legend>
<?php echo $this->element('questionnaires/input', array('questionnaire' => $questionnaire)); ?>
	</fieldset>
<?php echo $this->Form->end(); ?>
</div>

<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(sprintf(__('Edit %s', true), __('Questionnaire', true)), array('action' => 'edit', 'questionnaire' => $questionnaire['Questionnaire']['id'], 'return' => true)); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('Delete %s', true), __('Questionnaire', true)), array('action' => 'delete', 'questionnaire' => $questionnaire['Questionnaire']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $questionnaire['Questionnaire']['id'])); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('List %s', true), __('Questionnaires', true)), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('New %s', true), __('Questionnaire', true)), array('action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php printf(__('Related %s', true), __('Events', true));?></h3>
	<?php if (!empty($questionnaire['Event'])):?>
	<table class="list">
	<tr>
		<th><?php __('Name'); ?></th>
		<th><?php __('Open'); ?></th>
		<th><?php __('Close'); ?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($questionnaire['Event'] as $event):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $this->Html->link ($event['name'], array('controller' => 'events', 'action' => 'view', 'event' => $event['id']));?></td>
			<td><?php echo $this->ZuluruTime->fulldatetime ($event['open']);?></td>
			<td><?php echo $this->ZuluruTime->fulldatetime ($event['close']);?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View', true), array('controller' => 'events', 'action' => 'view', 'event' => $event['id'])); ?>
				<?php echo $this->Html->link(__('Edit', true), array('controller' => 'events', 'action' => 'edit', 'event' => $event['id'], 'return' => true)); ?>
				<?php echo $this->Html->link(__('Delete', true), array('controller' => 'events', 'action' => 'delete', 'event' => $event['id'], 'return' => true), null, sprintf(__('Are you sure you want to delete # %s?', true), $event['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

</div>
