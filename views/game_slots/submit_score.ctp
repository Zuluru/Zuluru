<?php
$this->Html->addCrumb (__('Games', true));
$this->Html->addCrumb (__('Game Slot', true) . ' ' . $gameSlot['GameSlot']['id']);
$this->Html->addCrumb (__('Submit Game Results', true));
?>

<div class="games form">
<h2><?php  __('Submit Game Results'); ?></h2>

<p>Submit the results for the <?php
echo $this->ZuluruTime->date ($gameSlot['GameSlot']['game_date']) . ' ' .
	$this->ZuluruTime->time ($gameSlot['GameSlot']['game_start']);
?> at <?php
echo $gameSlot['Field']['long_name'];
?>
.</p>

<?php
echo $this->Form->create(false, array('url' => Router::normalize($this->here)));

echo $this->Form->input("Game.status", array(
		'id' => 'Status',
		'label' => __('This game was:', true),
		'options' => array(
			'normal'			=> 'Played',
			'cancelled'			=> 'Cancelled (e.g. due to weather)',
		),
));
?>

<table class="list" id="Scores">
<tr>
	<th>Team Name</th>
	<th>Score</th>
<?php if (Configure::read('scoring.incident_reports')): ?>
	<th>Incident</th>
<?php endif; ?>
</tr>
<?php foreach ($gameSlot['Game'] as $game): ?>
<tr>
	<td><?php echo $game['HomeTeam']['name']; ?></td>
	<td><?php echo $this->ZuluruForm->input("Game.{$game['home_team']}.home_score", array(
			'div' => false,
			'class' => 'score',
			'label' => false,
			'type' => 'number',
			'size' => 3,
	)); ?></td>
<?php if (Configure::read('scoring.incident_reports')): ?>
	<td><?php
	echo $this->Form->input("Game.{$game['home_team']}.incident", array(
			'class' => 'incident_checkbox',
			'type' => 'checkbox',
			'value' => '1',
			'label' => false,
	));
	echo $this->Form->hidden("Game.{$game['home_team']}.type");
	echo $this->Form->hidden("Game.{$game['home_team']}.details");
	$this->Js->buffer("jQuery('#Game{$game['home_team']}Incident').data('team_id', {$game['home_team']});");
	?></td>
<?php endif; ?>
</tr>
<?php endforeach; ?>
</table>

<div class="submit">
<?php echo $this->Form->submit('Submit', array('div' => false)); ?>

<?php echo $this->Form->submit('Reset', array('div' => false, 'type' => 'reset')); ?>

<?php echo $this->Form->end(); ?>
</div>

<?php if (Configure::read('scoring.incident_reports')): ?>
<div id="IncidentDialog" title="Incident Details" class="form">
<div id="zuluru">
<form>
<?php
echo $this->Form->hidden('Incident.team');
echo $this->Form->input('Incident.type', array(
		'label' => 'Incident Type',
		'options' => Configure::read('options.incident_types'),
		'empty' => '---',
));
echo $this->Form->input('Incident.details', array(
		'label' => 'Enter the details of the incident',
		'cols' => 60,
));
?>
</form>
</div>
</div>
<?php endif; ?>

</div>

<?php
echo $this->Html->scriptBlock("
function statusChanged() {
	if (jQuery('#Status').val() == 'normal') {
		enableCommon();
		enableScores();
	} else {
		jQuery('.score').val(0);
		disableCommon();
		disableScores();
	}
}

function disableScores() {
	jQuery('#Scores').css('display', 'none');
}

function enableScores() {
	jQuery('#Scores').css('display', '');
}

function disableCommon() {
	jQuery('input:text').prop('disabled', true);
	jQuery('input[type=\"number\"]').prop('disabled', true);
	jQuery('.incident_checkbox').prop('disabled', true);
}

function enableCommon() {
	jQuery('input:text').prop('disabled', false);
	jQuery('input[type=\"number\"]').prop('disabled', false);
	jQuery('.incident_checkbox').prop('disabled', false);
}

function incidentCheckboxChanged(checkbox) {
	var team = checkbox.data('team_id');
	if (checkbox.prop('checked')) {
		jQuery('#IncidentTeam').val(team);
		jQuery('#IncidentType').val(jQuery('#Game' + team + 'Type').val());
		jQuery('#IncidentDetails').val(jQuery('#Game' + team + 'Details').val());
		jQuery('#IncidentDialog').dialog('open');
	}
}

function updateIncident() {
	var team = jQuery('#IncidentTeam').val();
	jQuery('#Game' + team + 'Type').val(jQuery('#IncidentType').val());
	jQuery('#Game' + team + 'Details').val(jQuery('#IncidentDetails').val());
}
");

// Make sure things are set up correctly, in the case that
// invalid data was detected and the form re-displayed.
$this->Js->buffer('
jQuery("#Status").on("change", function(){statusChanged();});
jQuery(".incident_checkbox").on("change", function(){incidentCheckboxChanged(jQuery(this));});
jQuery("#IncidentDialog").dialog({
		autoOpen: false,
		buttons: {
			"Continue": function() {
				jQuery(this).dialog("close");
				updateIncident();
			},
			"Cancel": function() { jQuery(this).dialog("close"); }
		},
		modal: true,
		resizable: false,
		width: 500
});
statusChanged();
');
?>
