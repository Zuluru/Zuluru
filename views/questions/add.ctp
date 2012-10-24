<div class="questions form">
<?php echo $this->Form->create('Question');?>
	<fieldset>
 		<legend><?php printf(__('Add %s', true), __('Question', true)); ?></legend>
	<?php
		echo $this->ZuluruForm->input('name', array(
			'size' => 60,
			'after' => $this->Html->para (null, __('A short name for this question, to be used as a heading in administrative reports.', true)),
		));
		echo $this->ZuluruForm->input('affiliate_id', array(
			'options' => $affiliates,
			'hide_single' => true,
			'empty' => '---',
		));
		echo $this->ZuluruForm->input('question', array(
			'cols' => 60,
			'after' => $this->Html->para (null, __('The full text of the question, to be shown to users.', true)),
			'class' => 'mceAdvanced',
		));
		echo $this->ZuluruForm->input('type', array(
			'options' => Configure::read('options.question_types'),
			'empty' => true,
		));
		echo $this->ZuluruForm->input('anonymous', array(
			'label' => 'Anonymous results',
			'after' => $this->Html->para (null, __('Will responses to this question be kept anonymous?', true)),
		));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(sprintf(__('List %s', true), __('Questions', true)), array('action' => 'index'));?></li>
	</ul>
</div>
<?php if (Configure::read('feature.tiny_mce')) $this->TinyMce->editor('advanced'); ?>
