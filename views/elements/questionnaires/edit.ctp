<table id="Questions" class="sortable list">
<thead>
	<tr>
		<th><?php __('Question'); ?></th>
		<th><?php __('Required'); ?></th>
		<th><?php __('Actions'); ?></th>
	</tr>
</thead>
<tbody>
<?php $i = 0; ?>
<?php
foreach ($questionnaire['Question'] as $question) {
	echo $this->element('questionnaires/edit_question', compact('questionnaire', 'question', 'i'));
	++$i;
}
?>

</tbody>
</table>

<div id="AddQuestionDiv" title="Add Question">
<p><?php __('Type part of the question you want'); ?></p>
<?php
	echo $this->Form->input ('Add.question', array(
			'type' => 'text',
			'autocomplete' => 'off',
			'label' => false,
			'size' => 50,
	));
?>
</div>

<div class="actions">
	<ul>
		<li><?php
		echo $this->Html->link('Add an existing question to this questionnaire', '#', array(
				'onclick' => 'return addQuestion();'
		));
		?></li>
	</ul>
</div>

<?php
// Make the table sortable
$this->ZuluruHtml->css('jquery.autocomplete', null, array('inline' => false));
$this->ZuluruHtml->script (array('jquery.tableSort', 'jquery.autocomplete', 'questionnaire'), array('inline' => false));
$add_question_url = $this->Html->url (array('controller' => 'questionnaires', 'action' => 'add_question', 'questionnaire' => $questionnaire['Questionnaire']['id']));
$auto_complete_url = $this->Html->url (array('controller' => 'questions', 'action' => 'autocomplete', 'affiliate' => $questionnaire['Questionnaire']['affiliate_id']));
$this->Js->buffer ("
	var last_index = $i;
	jQuery('.sortable').tableSort(tableReorder);

	jQuery('#AddQuestionDiv').dialog({
		autoOpen: false,
		buttons: { 'Cancel': function() { jQuery(this).dialog('close'); } },
		modal: true,
		resizable: false,
		width: 500
	});

	jQuery('#AddQuestion').autocomplete('$auto_complete_url',
	{
		mustMatch: true,
		width: 470,
		scroll: true
	}).result(function(event, data, formatted)
	{
		if (data !== undefined)
		{
			addQuestionFinish('$add_question_url', data, ++last_index);
			jQuery('#AddQuestionDiv').dialog('close');
		}
	});
");
?>
