<?php
$this->Html->addCrumb (__('Question', true));
$this->Html->addCrumb (__('Edit', true));
?>

<div class="questions form">
<?php echo $this->Form->create('Question');?>
	<fieldset>
 		<legend><?php printf(__('Edit %s', true), __('Question', true)); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('question', array(
				'cols' => 60,
		));
		echo $this->Form->input('type', array(
				'options' => Configure::read('options.question_types'),
				'empty' => true,
		));
	?>
	<?php
	$type = $this->data['Question']['type'];
	if ($type == 'radio' || $type == 'select'):
	?>
		<table id="Answers" class="sortable">
		<thead>
			<tr>
				<th></th>
				<th>Answer</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
		<?php $i = 0; ?>
		<?php
		foreach ($this->data['Answer'] as $answer) {
			++$i;
			echo $this->element('question/edit_answer', compact('answer', 'i'));
		}
		?>

		</tbody>
		</table>

		<div class="actions">
			<ul>
				<li><?php
				echo $this->Html->link('Add an answer to this question', '#', array(
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
$this->ZuluruHtml->script (array('jquery.tableSort', 'questionnaire'), array('inline' => false));
$url = $this->Html->url (array('controller' => 'questions', 'action' => 'add_answer'));
$this->Js->buffer ("
	$('.sortable').tableSort(tableReorder);
");
echo $this->Html->scriptBlock("
	var last_index = $i;
	function addAnswerLocal() {
		return addAnswer('$url', {$this->data['Question']['id']}, ++last_index);
	}
");
?>
