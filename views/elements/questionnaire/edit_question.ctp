<?php
$class = null;
if ($i % 2 == 1) {
	$class = ' class="altrow"';
}
?>
<tr<?php echo $class;?>>
	<td class="handle"><?php
	echo $this->Form->hidden("Question.$i.question_id", array('value' => $question['id']));
	echo $this->Form->hidden("Question.$i.sort", array('value' => $i));
	echo $question['question'] . ' (' . $question['type'] . ')' .
		($question['anonymous'] ? (' (' . __('anonymous', true) . ')') : '');
	?></td>
	<td><?php
	$checked = (array_key_exists ('QuestionnairesQuestion', $question) ? $question['QuestionnairesQuestion']['required'] : false);
	echo $this->Form->input("Question.$i.required", array(
			'div' => false,
			'label' => false,
			'type' => 'checkbox',
			'checked' => $checked ? 'checked' : false,
	));
	?></td>
	<td class="actions"><?php
	echo $this->Html->link ('Edit', array('controller' => 'questions', 'action' => 'edit', 'question' => $question['id']));
	echo $this->Html->link ('Remove', '#');
	$id = 'span_' . mt_rand(); ?>
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
