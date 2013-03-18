<?php
$this->Html->addCrumb (__('Games', true));
$this->Html->addCrumb (__('Game', true) . ' ' . $game['Game']['id']);
$this->Html->addCrumb (__('Submit Game Stats', true));
?>

<?php
if ($team_id == $game['HomeTeam']['id'] || $team_id === null) {
	$this_team = $game['HomeTeam'];
	$opponent = $game['AwayTeam'];
} else {
	$this_team = $game['AwayTeam'];
	$opponent = $game['HomeTeam'];
}
?>

<div class="games form">
<h2><?php  __('Submit Game Stats'); ?></h2>

<p>Submit the stats for the <?php
echo $this->ZuluruTime->date ($game['GameSlot']['game_date']) . ' ' .
	$this->ZuluruTime->time ($game['GameSlot']['game_start']);
?> at <?php
echo $game['GameSlot']['Field']['long_name'];
?> between <?php
echo $this->element('teams/block', array('team' => $this_team, 'show_shirt' => false));
?> and <?php
echo $this->element('teams/block', array('team' => $opponent, 'show_shirt' => false));
?>
.</p>

<p><?php
if (Game::_is_finalized($game)) {
	$msg = 'The score for this game has been confirmed as %s %d, %s %d.';
	if ($team_id === null || $team_id == $game['HomeTeam']['id']) {
		$this_team['score'] = $game['Game']['home_score'];
		$opponent['score'] = $game['Game']['away_score'];
	} else {
		$this_team['score'] = $game['Game']['away_score'];
		$opponent['score'] = $game['Game']['home_score'];
	}
} else if ($team_id !== null) {
	$msg = 'You have submitted the score for this game as %s %d, %s %d, but this has not been confirmed by your opponent.';
	$this_team['score'] = $game['ScoreEntry'][$team_id]['score_for'];
	$opponent['score'] = $game['ScoreEntry'][$team_id]['score_against'];
} else {
	$msg = 'A score of %s %d, %s %d has been submitted for this game, but this has not been confirmed.';
	$entry = current($game['ScoreEntry']);
	if ($entry['team_id'] == $this_team['id']) {
		$this_team['score'] = $entry['score_for'];
		$opponent['score'] = $entry['score_against'];
	} else {
		$this_team['score'] = $entry['score_against'];
		$opponent['score'] = $entry['score_for'];
	}
}
printf(__($msg, true), $this_team['name'], $this_team['score'], $opponent['name'], $opponent['score']);
?>
</p>

<?php echo $this->element("games/stats_entry/{$game['Division']['League']['sport']}"); ?>

<p>
<?php
echo $this->Html->link(__('Show Only Applicable Stat Options', true), '#', array('class' => 'show_applicable'));
echo $this->Html->link(__('Show All Stat Options', true), '#', array('class' => 'show_unapplicable'));
?>

<?php if (Configure::read('feature.attendance')): ?>
/

<?php
echo $this->Html->link(__('Show All Players', true), '#', array('class' => 'show_all'));
echo $this->Html->link(__('Show Only Attending Players', true), '#', array('class' => 'show_attending'));
?>
<?php endif; ?>
</p>

<?php
echo $this->Form->create('Stat', array('url' => Router::normalize($this->here)));
$stats = Set::extract('/Division/League/StatType/.', $game);

if (isset($attendance)) {
	echo $this->element('games/stats_entry', compact('stats', 'attendance'));
} else {
	echo $this->Html->tag('h3', $this->element('teams/block', array('team' => $game['HomeTeam'], 'show_shirt' => false)));
	echo $this->element('games/stats_entry', array('stats' => $stats, 'attendance' => $home_attendance));
	echo $this->Html->tag('h3', $this->element('teams/block', array('team' => $game['AwayTeam'], 'show_shirt' => false)));
	echo $this->element('games/stats_entry', array('stats' => $stats, 'attendance' => $away_attendance));
}
?>

<p>
<?php
echo $this->Html->link(__('Show Only Applicable Stat Options', true), '#', array('class' => 'show_applicable'));
echo $this->Html->link(__('Show All Stat Options', true), '#', array('class' => 'show_unapplicable'));
?>

<?php if (Configure::read('feature.attendance')): ?>
/

<?php
echo $this->Html->link(__('Show All Players', true), '#', array('class' => 'show_all'));
echo $this->Html->link(__('Show Only Attending Players', true), '#', array('class' => 'show_attending'));
?>
<?php endif; ?>
</p>

<div class="submit">
<?php
if (isset($attendance)) {
	echo $this->Form->submit('Submit', array('div' => false, 'onClick' => "return check_score({$this_team['score']}, {$opponent['score']}, {$this_team['id']});"));
} else {
	echo $this->Form->submit('Submit', array('div' => false, 'onClick' => "return check_score({$this_team['score']}, {$opponent['score']}, {$this_team['id']}) && check_score({$opponent['score']}, {$this_team['score']}, {$opponent['id']});"));
}
?>

<?php echo $this->Form->submit('Reset', array('div' => false, 'type' => 'reset')); ?>

<?php echo $this->Form->end(); ?>
</div>
</div>

<?php
$this->ZuluruHtml->script (array('stats', "sport_{$game['Division']['League']['sport']}"), array('inline' => false));
$stat_js = array();
foreach ($stats as $stat) {
	if (!empty($stat['validation'])) {
		$func = "validate_{$stat['validation']}";
		if (method_exists($sport_obj, $func)) {
			$stat_js = array_merge($stat_js, $sport_obj->$func($stat));
		} else {
			trigger_error("Validation handler {$stat['validation']} was not found in the {$game['Division']['League']['sport']} component!", E_USER_ERROR);
		}
	}
}
echo $this->Html->scriptBlock("
function check_score(team_score, opponent_score, team_id) {
	var alert_msg = '';
	var confirm_msg = '';
	" . implode("\n	", $stat_js) . "
	if (alert_msg != '') {
		alert(alert_msg + '\\n\\nPlease correct this and re-submit.');
		return false;
	}
	if (confirm_msg != '') {
		return confirm(confirm_msg + '\\n\\nClick OK to proceed, or Cancel to enter more stats.');
	}
	return true;
}
");

$this->Js->get('.show_applicable')->event('click', 'showApplicable();');
$this->Js->get('.show_unapplicable')->event('click', 'showUnapplicable();');

$this->Js->buffer("
jQuery('input').change(function(){inputChanged(jQuery(this));});
jQuery('tr#sub_row').find('input[class^=stat_]').each(function() { inputChanged(jQuery(this)); });
showApplicable();
");

if (Configure::read('feature.attendance')) {
	$attending = ATTENDANCE_ATTENDING;

	$this->Js->get('.show_all')->event('click', 'showAll();');
	$this->Js->get('.show_attending')->event('click', "showAttending('$attending');");

	$this->Js->buffer("showAttending('$attending');");

	echo $this->element('games/attendance_div');
}
?>
