<?php
$class = null;
if ($i % 2 == 1) {
	$class = ' class="altrow"';
}
$tr_id = 'tr_' . mt_rand();
?>
<tr id="<?php echo $tr_id; ?>"<?php echo $class;?>>
	<td class="handle"><?php
	echo $this->Form->hidden("Question.$i.id", array('value' => $question['id']));
	echo $this->Form->hidden("Question.$i.question", array('value' => $question['question']));
	echo $this->Form->hidden("Question.$i.type", array('value' => $question['type']));
	echo $this->Form->hidden("Question.$i.active");
	echo $this->Form->hidden("Question.$i.anonymous");
	echo $this->Form->hidden("Question.$i.QuestionnairesQuestion.question_id", array('value' => $question['id']));
	echo $this->Form->hidden("Question.$i.QuestionnairesQuestion.sort");
	echo $question['question'] . ' (' . $question['type'] . ')' .
		($question['anonymous'] ? (' (' . __('anonymous', true) . ')') : '');
	?></td>
	<td><?php
	echo $this->Form->input("Question.$i.QuestionnairesQuestion.required", array(
			'div' => false,
			'label' => false,
			'type' => 'checkbox',
	));
	?></td>
	<td class="actions"><?php
	echo $this->Html->link (__('Edit', true), array('controller' => 'questions', 'action' => 'edit', 'question' => $question['id']));
	echo $this->Js->link (__('Remove', true),
			array('action' => 'remove_question', 'questionnaire' => $questionnaire['Questionnaire']['id'], 'question' => $question['id'], 'id' => $tr_id),
			array('update' => "#temp_update")
	);
	$id = 'span_' . mt_rand();
	?>
	<span id="<?php echo $id; ?>">
	<?php
	if ($question['active']) {
		echo $this->Js->link(__('Deactivate', true),
				array('controller' => 'questions', 'action' => 'deactivate', 'question' => $question['id'], 'id' => $id),
				array('update' => "#temp_update")
		);
	} else {
		echo $this->Js->link(__('Activate', true),
				array('controller' => 'questions', 'action' => 'activate', 'question' => $question['id'], 'id' => $id),
				array('update' => "#temp_update")
		);
	}
	?>
	</span>
	</td>
</tr>
