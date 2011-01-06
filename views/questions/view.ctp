<div class="questions view">
<h2><?php  __('Question');?></h2>
<?php echo $this->element('question/input', compact('question')); ?>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(sprintf(__('Edit %s', true), __('Question', true)), array('action' => 'edit', 'question' => $question['Question']['id'])); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php printf(__('Related %s', true), __('Questionnaires', true));?></h3>
	<?php if (!empty($question['Questionnaire'])):?>
	<table cellpadding = "0" cellspacing = "0">
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
				<?php echo $this->Html->link(__('Edit', true), array('controller' => 'questionnaires', 'action' => 'edit', 'questionnaire' => $questionnaire['id'])); ?>
				<?php echo $this->Html->link(__('Delete', true), array('controller' => 'questionnaires', 'action' => 'delete', 'questionnaire' => $questionnaire['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $questionnaire['id'])); ?>
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
