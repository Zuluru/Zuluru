<?php
$field = array(
	'radio' => 'answer_id',
	'select' => 'answer_id',
	'checkbox' => 'answer_id',
	'text' => 'answer',
	'textbox' => 'answer',
);

if (array_key_exists ('Question', $question)) {
	$details = $question['Question'];
} else {
	$details = $question;
}

$options = array(
	'label' => @$details['question'],
	'required' => @$details['required'] || @$question['QuestionnairesQuestion']['required'],
	'type' => $details['type'],
);
if (array_key_exists ('after', $details)) {
	$options['after'] = $this->Html->para (null, $details['after']);
}

switch ($details['type'])
{
	case 'radio':
		$key = "Response.{$details['id']}";
		$options['legend'] = $details['question'];
		$options['options'] = Set::combine ($question['Answer'], '{n}.id', '{n}.answer');
		$item = $this->Form->hidden ("$key.question_id", array('value' => $details['id'])) .
			$this->Form->input ("$key.{$field[$details['type']]}", $options);
		break;

	case 'select':
	case 'checkbox':
		$key = "Response.{$details['id']}";
		$options['options'] = Set::combine ($question['Answer'], '{n}.id', '{n}.answer');
		$options['empty'] = '---';
		$item = $this->Form->hidden ("$key.question_id", array('value' => $details['id'])) .
			$this->Form->input ("$key.{$field[$details['type']]}", $options);
		break;

	case 'text':
		$key = "Response.{$details['id']}";
		$options['size'] = 75;
		$item = $this->Form->hidden ("$key.question_id", array('value' => $details['id'])) .
			$this->Form->input ("$key.{$field[$details['type']]}", $options);
		break;

	case 'textbox':
		$key = "Response.{$details['id']}";
		$options['cols'] = 72;
		$item = $this->Form->hidden ("$key.question_id", array('value' => $details['id'])) .
			$this->Form->input ("$key.{$field[$details['type']]}", $options);
		break;

	case 'group_start':
		$item = "<fieldset><legend>{$details['question']}</legend>\n";
		break;

	case 'group_end':
		$item = "</fieldset>\n";
		break;

	case 'description':
		$item = $this->Html->tag ('label', $details['question']);
		break;

	case 'label':
		$item = $this->Html->tag ('label', $details['question']);
		break;
}

if (isset ($this->data) &&
	array_key_exists ('Response', $this->data) &&
	array_key_exists ('id', $details) &&
	array_key_exists ($details['id'], $this->data['Response']) &&
	array_key_exists ('id', $this->data['Response'][$details['id']]))
{
	$item .= $this->Form->hidden ("$key.id");
}

echo $item;
?>
