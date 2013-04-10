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

<p>Submit the result for the <?php
echo $this->ZuluruTime->date ($game['GameSlot']['game_date']) . ' ' .
	$this->ZuluruTime->time ($game['GameSlot']['game_start']);
?> at <?php
echo $game['GameSlot']['Field']['long_name'];
?> between <?php
echo $this_team['name'];
?> and <?php
echo $opponent['name'];
?>
.</p>
<p>If your opponent has already entered a result, it will be displayed below.  If the result you enter does not agree with this result posting of the result will be delayed until your coordinator can confirm the correct result.</p>

<?php
echo $this->Form->create(false, array('url' => Router::normalize($this->here)));

if ($opponent_score) {
	$default_status = $opponent_score['status'];
} else {
	$default_status = null;
}
echo $this->Form->input("ScoreEntry.$team_id.status", array(
		'id' => 'Status',
		'label' => __('This game was:', true),
		'options' => array(
			'normal'			=> 'Played',
			'home_default'		=> "Defaulted by {$game['HomeTeam']['name']}",
			'away_default'		=> "Defaulted by {$game['AwayTeam']['name']}",
			'cancelled'			=> 'Cancelled (e.g. due to weather)',
		),
		'default' => $default_status,
));

if (array_key_exists ($team_id, $game['ScoreEntry'])) {
	echo $this->Form->hidden ("ScoreEntry.$team_id.id", array ('value' => $game['ScoreEntry'][$team_id]['id']));
}
?>

<table class="list" id="Scores">
<tr>
	<th>Team Name</th>
	<th>Your Score Entry</th>
	<th>Opponent's Score Entry</th>
</tr>
<tr>
	<td><?php echo $this_team['name']; ?></td>
	<td><?php echo $this->ZuluruForm->input("ScoreEntry.$team_id.score_for", array(
			'div' => false,
			'id' => ($team_id == $game['HomeTeam']['id'] ? 'ScoreHome' : 'ScoreAway'),
			'label' => false,
			'type' => 'number',
			'size' => 3,
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
	<td><?php echo $this->ZuluruForm->input("ScoreEntry.$team_id.score_against", array(
			'div' => false,
			'id' => ($team_id == $game['HomeTeam']['id'] ? 'ScoreAway' : 'ScoreHome'),
			'label' => false,
			'type' => 'number',
			'size' => 3,
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
if (League::hasSpirit($game['Division']['League'])) {
	echo $this->element ('spirit/input', array('team_id' => $opponent['id'],
			'created_team_id' => $this_team['id'], 'game' => $game, 'spirit_obj' => $spirit_obj));
}
?>

<?php if (Configure::read('scoring.incident_reports')): ?>
<div id="IncidentWrapper">
<?php
	echo $this->Form->input('Game.incident', array(
			'type' => 'checkbox',
			'value' => '1',
			'label' => 'I have an incident to report',
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
</div>
<?php endif; ?>

<?php if ($game['Division']['allstars'] != 'never'): ?>
<div id="AllstarWrapper">
<?php
if ($game['Division']['allstars'] == 'optional') {
	echo $this->Form->input('Game.allstar', array(
			'type' => 'checkbox',
			'value' => '1',
			'label' => 'I want to nominate an all-star',
	));
}

if ($game['Division']['ratio'] == 'womens') {
	$genders = 'one female';
} else if ($game['Division']['ratio'] == 'mens') {
	$genders = 'one male';
} else {
	$genders = 'one male and/or one female';
}
?>
<fieldset class="AllstarDetails">
<legend>Allstar Nominations</legend>
<p>You may select <?php echo $genders; ?> all-star from the list below<?php
if ($game['Division']['allstars'] == 'always') {
	echo ', if you think they deserve to be nominated as an all-star.';
}
?>.</p>

<?php
// Build list of allstar options
$players = array();
$player_roles = Configure::read('playing_roster_roles');

if ($game['Division']['allstars_from'] == 'submitter') {
	$roster = $this_team['Person'];
} else {
	$roster = $opponent['Person'];
}

foreach ($roster as $person) {
	$block = $this->element('people/block', array('person' => $person, 'link' => false));
	if (!in_array($person['TeamsPerson']['role'], $player_roles)) {
		$block .= ' (' . __('substitute', true) . ')';
	}
	$players[$person['gender']][$person['id']] = $block;
}

// May need to tweak saved allstar data
$male = $female = null;
if (array_key_exists ('division_id', $this->data['Game']) && !empty($this->data['Allstar'])) {
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
if (! empty ($game['Division']['League']['coord_list'])) {
	$coordinator = $this->Html->link($coordinator, "mailto:{$game['Division']['League']['coord_list']}");
}
?>

<p>If you feel strongly about nominating a second male or female please contact your <?php echo $coordinator; ?>.</p>
</fieldset>
</div>
<?php endif; ?>

<?php if (League::hasStats($game['Division']['League'])): ?>
<div id="StatsWrapper">
<?php
	echo $this->Form->input('Game.collect_stats', array(
			'type' => 'checkbox',
			'value' => '1',
			'label' => 'I want to enter stats for this game (if you don\'t do it now, you can do it later)',
	));
?>
</div>
<?php endif; ?>

<div class="submit">
<?php echo $this->Form->submit('Submit', array('div' => false)); ?>

<?php echo $this->Form->submit('Reset', array('div' => false, 'type' => 'reset')); ?>

<?php echo $this->Form->end(); ?>
</div>
</div>

<?php
// There is no harm in calling jQuery functions on empty lists, so we don't
// have to specially account for the cases where the incident or allstar
// checkboxes don't exist.
// Note that the spirit scoring objects must implement the enableSpirit and
// disableSpirit JavaScript functions to handle any non-text input fields.
$win = Configure::read('scoring.default_winning_score');
$lose = Configure::read('scoring.default_losing_score');
echo $this->Html->scriptBlock("
function statusChanged() {
	if (jQuery('#Status').val() == 'home_default') {
		jQuery('#ScoreHome').val($lose);
		jQuery('#ScoreAway').val($win);
		disableCommon();
		enableScores();
	} else if (jQuery('#Status').val() == 'away_default') {
		jQuery('#ScoreHome').val($win);
		jQuery('#ScoreAway').val($lose);
		disableCommon();
		enableScores();
	} else if (jQuery('#Status').val() == 'normal') {
		enableCommon();
		enableScores();
	} else {
		jQuery('#ScoreHome').val(0);
		jQuery('#ScoreAway').val(0);
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
	jQuery('input:text').attr('disabled', 'disabled');
	jQuery('#GameIncident').attr('disabled', 'disabled');
	jQuery('#IncidentWrapper').css('display', 'none');
	jQuery('#GameAllstar').attr('disabled', 'disabled');
	jQuery('#AllstarWrapper').css('display', 'none');
	if (typeof window.disableSpirit == 'function') {
		disableSpirit();
	}
}

function enableCommon() {
	jQuery('input:text').removeAttr('disabled');
	jQuery('#GameIncident').removeAttr('disabled');
	jQuery('#IncidentWrapper').css('display', '');
	jQuery('#GameAllstar').removeAttr('disabled');
	jQuery('#AllstarWrapper').css('display', '');
	if (typeof window.enableSpirit == 'function') {
		enableSpirit();
	}
}

function incidentCheckboxChanged() {
	if (jQuery('#GameIncident').attr('checked')) {
		jQuery('#IncidentDetails').css('display', '');
	} else {
		jQuery('#IncidentDetails').css('display', 'none');
	}
}

function allstarCheckboxChanged() {
	if (jQuery('#GameAllstar').attr('checked')) {
		jQuery('.AllstarDetails').css('display', '');
	} else {
		jQuery('.AllstarDetails').css('display', 'none');
	}
}
");

// Make sure things are set up correctly, in the case that
// invalid data was detected and the form re-displayed.
// Not sure what might be invalid if a "defaulted" status is
// selected, since pretty much everything else is disabled,
// but maybe something in the future. Cost to do this is
// extremely minimal.
$this->Js->buffer('
jQuery("#Status").change(function(){statusChanged();});
jQuery("#GameIncident").change(function(){incidentCheckboxChanged();});
jQuery("#GameAllstar").change(function(){allstarCheckboxChanged();});
statusChanged();
incidentCheckboxChanged();
');
if ($game['Division']['allstars'] == 'optional') {
	$this->Js->buffer('allstarCheckboxChanged();');
}

?>
