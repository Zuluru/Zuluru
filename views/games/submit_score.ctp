<?php
$this->Html->addCrumb (__('Games', true));
$this->Html->addCrumb (__('Game', true) . ' ' . $game['Game']['id']);
$this->Html->addCrumb (__('Submit Game Results', true));
?>

<?php
if ($team_id == $game['HomeTeam']['id']) {
	$this_team = $game['HomeTeam'];
	$opponent = $game['AwayTeam'];
} else {
	$this_team = $game['AwayTeam'];
	$opponent = $game['HomeTeam'];
}
$opponent_score = Game::_get_score_entry($game, $opponent['id']);
?>

<div class="games form">
<h2><?php  __('Submit Game Results'); ?></h2>

<p>Submit the score for the <?php
echo $this->ZuluruTime->date ($game['GameSlot']['game_date']) . ' ' .
	$this->ZuluruTime->time ($game['GameSlot']['game_start']);
?> at <?php
echo "{$game['GameSlot']['Field']['code']} {$game['GameSlot']['Field']['num']}";
?> between <?php
echo $this_team['name'];
?> and <?php
echo $opponent['name'];
?>
.</p>
<p>If your opponent has already entered a score, it will be displayed below.  If the score you enter does not agree with this score, posting of the score will be delayed until your coordinator can confirm the correct score.</p>

<?php
echo $this->Form->create(false, array('url' => Router::normalize($this->here)));
echo $this->Form->hidden("ScoreEntry.$team_id.defaulted", array('value' => 'no'));

if (array_key_exists ($team_id, $game['ScoreEntry'])) {
	echo $this->Form->hidden ("ScoreEntry.$team_id.id", array ('value' => $game['ScoreEntry'][$team_id]['id']));
}
?>

<table class="list">
<tr>
	<th>Team Name</th>
	<th>Defaulted?</th>
	<th>Your Score Entry</th>
	<th>Opponent's Score Entry</th>
</tr>
<tr>
	<td><?php echo $this_team['name']; ?></td>
	<td><?php echo $this->Form->input("ScoreEntry.$team_id.defaulted", array(
			'div' => false,
			'id' => 'WeDefaulted',
			'label' => false,
			'type' => 'checkbox',
			'value' => 'us',
			'hiddenField' => false,
			'onclick' => 'defaultCheckboxChanged()',
	)); ?></td>
	<td><?php echo $this->Form->input("ScoreEntry.$team_id.score_for", array(
			'div' => false,
			'id' => 'ScoreFor',
			'label' => false,
			'size' => 2,
	)); ?></td>
	<td><?php
	if ($opponent_score) {
		echo $opponent_score['score_against'];
	} else {
		__('not yet entered');
	}
	?></td>
</tr>
<tr>
	<td><?php echo $opponent['name']; ?></td>
	<td><?php echo $this->Form->input("ScoreEntry.$team_id.defaulted", array(
			'div' => false,
			'id' => 'TheyDefaulted',
			'label' => false,
			'type' => 'checkbox',
			'value' => 'them',
			'hiddenField' => false,
			'onclick' => 'defaultCheckboxChanged()',
	)); ?></td>
	<td><?php echo $this->Form->input("ScoreEntry.$team_id.score_against", array(
			'div' => false,
			'id' => 'ScoreAgainst',
			'label' => false,
			'size' => 2,
	)); ?></td>
	<td><?php
	if ($opponent_score) {
		echo $opponent_score['score_for'];
	} else {
		__('not yet entered');
	}
	?></td>
</tr>
</table>

<?php
echo $this->element ('spirit/input', array('team_id' => $opponent['id'],
		'created_team_id' => $this_team['id'], 'game' => $game, 'spirit_obj' => $spirit_obj));
?>

<div id="IncidentWrapper">
<?php
if (Configure::read('scoring.incident_reports')):
	echo $this->Form->input('Game.incident', array(
			'type' => 'checkbox',
			'value' => '1',
			'label' => 'I have an incident to report',
			'onclick' => 'incidentCheckboxChanged()',
	));
?>
<fieldset id="IncidentDetails">
<legend>Incident Details</legend>
<?php
echo $this->Form->input("Incident.$team_id.type", array(
		'label' => 'Incident Type',
		'options' => Configure::read('options.incident_types'),
		'empty' => '---',
));
echo $this->Form->input("Incident.$team_id.details", array(
		'label' => 'Enter the details of the incident',
		'cols' => 60,
));
?>
</fieldset>
<?php endif; ?>
</div>

<div id="AllstarWrapper">
<?php
if ($game['League']['allstars'] == 'optional') {
	echo $this->Form->input('Game.allstar', array(
			'type' => 'checkbox',
			'value' => '1',
			'label' => 'I want to nominate an all-star',
			'onclick' => 'allstarCheckboxChanged()',
	));
}
if ($game['League']['allstars'] != 'never'):
	if ($game['League']['ratio'] == 'womens') {
		$genders = 'one female';
	} else if ($game['League']['ratio'] == 'mens') {
		$genders = 'one male';
	} else {
		$genders = 'one male and/or one female';
	}
?>
<fieldset id="AllstarDetails">
<legend>Allstar Nominations</legend>
<p>You may select <?php echo $genders; ?> all-star from the list below<?php
if ( $game['League']['allstars'] == 'always' ) {
	echo ', if you think they deserve to be nominated as an all-star.';
}
?>.</p>

<?php
// Build list of allstar options
$players = array();
foreach ($opponent['Person'] as $person) {
	$players[$person['gender']][$person['id']] = $this->element('people/block', compact('person'));
}

// May need to tweak saved allstar data
$male = $female = null;
if (array_key_exists ('Allstar', $this->data)) {
	$allstars = array();
	foreach ($this->data['Allstar'] as $allstar) {
		if (is_array ($players[$allstar['Person']['gender']])) {
			if (array_key_exists ($allstar['person_id'], $players[$allstar['Person']['gender']])) {
				if ($allstar['Person']['gender'] == 'Male') {
					$male = $allstar['Person']['id'];
					echo $this->Form->hidden('Allstar.0.id', array('value' => $allstar['id']));
				} else {
					$female = $allstar['Person']['id'];
					echo $this->Form->hidden('Allstar.1.id', array('value' => $allstar['id']));
				}
			}
		}
	}
}

if (!empty ($players['Male'])) {
	echo $this->Form->input('Allstar.0.person_id', array(
			'type' => 'radio',
			'legend' => __('Male', true),
			'options' => $players['Male'],
			'default' => $male,
	));
}

if (!empty ($players['Female'])) {
	echo $this->Form->input('Allstar.1.person_id', array(
			'type' => 'radio',
			'legend' => __('Female', true),
			'options' => $players['Female'],
			'default' => $female,
	));
}

$coordinator = __('league coordinator', true);
if (! empty ($game['League']['coord_list'])) {
	$coordinator = $this->Html->link($coordinator, "mailto:{$game['League']['coord_list']}");
}
?>

<p>If you feel strongly about nominating a second male or female please contact your <?php echo $coordinator; ?>.</p>
</fieldset>
<?php endif; ?>
</div>

<div class="submit">
<?php echo $this->Form->submit('Submit', array('div' => false)); ?>

<?php echo $this->Form->submit('Reset', array('div' => false, 'type' => 'reset')); ?>

<?php echo $this->Form->end(); ?>
</div>

<?php
// There is no harm in calling jQuery functions on empty lists, so we don't
// have to specially account for the cases where the incident or allstar
// checkboxes don't exist.
// Note that the spirit scoring objects must implement the enableSpirit and
// disableSpirit JavaScript functions to handle any non-text input fields.
// TODO: Use "us", "them" and "both" classes to simplify this further
$win = Configure::read('scoring.default_winning_score');
$lose = Configure::read('scoring.default_losing_score');
echo $this->Html->scriptBlock("
function defaultCheckboxChanged() {
	if ($('#WeDefaulted').attr('checked')) {
		$('#TheyDefaulted').attr('disabled', 'disabled');
		$('#ScoreFor').val($lose);
		$('#ScoreAgainst').val($win);
		disableCommon();
	} else if ($('#TheyDefaulted').attr('checked')) {
		$('#WeDefaulted').attr('disabled', 'disabled');
		$('#ScoreFor').val($win);
		$('#ScoreAgainst').val($lose);
		disableCommon();
	} else {
		$('#TheyDefaulted').removeAttr('disabled');
		$('#WeDefaulted').removeAttr('disabled');
		enableCommon();
	}
}

function disableCommon() {
	$('input:text').attr('disabled', 'disabled');
	$('#GameIncident').attr('disabled', 'disabled');
	$('#IncidentWrapper').css('display', 'none');
	$('#GameAllstar').attr('disabled', 'disabled');
	$('#AllstarWrapper').css('display', 'none');
	if (typeof window.disableSpirit == 'function') {
		disableSpirit();
	}
}

function enableCommon() {
	$('input:text').removeAttr('disabled');
	$('#GameIncident').removeAttr('disabled');
	$('#IncidentWrapper').css('display', '');
	$('#GameAllstar').removeAttr('disabled');
	$('#AllstarWrapper').css('display', '');
	if (typeof window.enableSpirit == 'function') {
		enableSpirit();
	}
}

function incidentCheckboxChanged() {
	if ($('#GameIncident').attr('checked')) {
		$('#IncidentDetails').css('display', '');
	} else {
		$('#IncidentDetails').css('display', 'none');
	}
}

function allstarCheckboxChanged() {
	if ($('#GameAllstar').attr('checked')) {
		$('#AllstarDetails').css('display', '');
	} else {
		$('#AllstarDetails').css('display', 'none');
	}
}
");

// Make sure things are set up correctly, in the case that
// invalid data was detected and the form re-displayed.
// Not sure what might be invalid if a "defaulted" box is
// checked, since pretty much everything else is disabled,
// but maybe something in the future. Cost to do this is
// extremely minimal.
$this->Js->buffer('defaultCheckboxChanged(); incidentCheckboxChanged();');
if ($game['League']['allstars'] == 'optional') {
	$this->Js->buffer('allstarCheckboxChanged();');
}

?>
