<?php
$this->Html->addCrumb (__('Question', true));
$this->Html->addCrumb (__('Edit', true));
?>

<div class="questions form">
<?php echo $this->Form->create('Question', array('url' => Router::normalize($this->here)));?>
	<fieldset>
 		<legend><?php printf(__('Edit %s', true), __('Question', true)); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->ZuluruForm->input('name', array(
			'size' => 60,
			'after' => $this->Html->para (null, __('A short name for this question, to be used as a heading in administrative reports.', true)),
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
	<?php
	$type = $this->data['Question']['type'];
	if ($type == 'radio' || $type == 'select' || $type = 'checkbox'):
	?>
		<table id="Answers" class="sortable list">
		<thead>
			<tr>
				<th></th>
				<th><?php __('Answer'); ?></th>
				<th><?php __('Actions'); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php $i = 0; ?>
		<?php
		foreach ($this->data['Answer'] as $answer) {
			++$i;
			echo $this->element('questions/edit_answer', compact('answer', 'i'));
		}
		?>

		</tbody>
		</table>

		<div class="actions">
			<ul>
				<li><?php
				echo $this->Html->link(__('Add an answer to this question', true), '#', array(
						'onclick' => "return addAnswerLocal();"
				));
				?></li>
			</ul>
		</div>
	<?php endif; ?>

	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>

<?php
// Make the table sortable
echo $this->ZuluruHtml->script (array('jquery.tableSort', 'questionnaire'), array('inline' => false));
$url = $this->Html->url (array('controller' => 'questions', 'action' => 'add_answer', 'question' => $this->data['Question']['id']));
$this->Js->buffer ("
	jQuery('.sortable').tableSort(tableReorder);
");
echo $this->Html->scriptBlock("
	var last_index = $i;
	function addAnswerLocal() {
		return addAnswer('$url', ++last_index);
	}
");
?>
<?php if (Configure::read('feature.tiny_mce')) $this->TinyMce->editor('advanced'); ?>
