<?php
$this->Html->addCrumb (__('Question', true));
$this->Html->addCrumb (__('View', true));
?>

<div class="questions view">
<h2><?php  __('Question');?></h2>
	<?php if (count($affiliates) > 1): ?>
	<dl><?php $i = 1; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Affiliate'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($question['Affiliate']['name'], array('controller' => 'affiliates', 'action' => 'view', 'affiliate' => $question['Affiliate']['id'])); ?>

		</dd>
	</dl>
	<?php endif; ?>
<?php echo $this->element('questions/input', compact('question')); ?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(sprintf(__('Edit %s', true), __('Question', true)), array('action' => 'edit', 'question' => $question['Question']['id'], 'return' => true)); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php printf(__('Related %s', true), __('Questionnaires', true));?></h3>
	<?php if (!empty($question['Questionnaire'])):?>
	<table class="list">
	<tr>
		<th><?php __('Id'); ?></th>
		<th><?php __('Name'); ?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($question['Questionnaire'] as $questionnaire):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $questionnaire['id'];?></td>
			<td><?php echo $questionnaire['name'];?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('Preview', true), array('controller' => 'questionnaires', 'action' => 'view', 'questionnaire' => $questionnaire['id'])); ?>
				<?php echo $this->Html->link(__('Edit', true), array('controller' => 'questionnaires', 'action' => 'edit', 'questionnaire' => $questionnaire['id'], 'return' => true)); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(sprintf(__('New %s', true), __('Questionnaire', true)), array('controller' => 'questionnaires', 'action' => 'add'));?> </li>
		</ul>
	</div>
</div>
