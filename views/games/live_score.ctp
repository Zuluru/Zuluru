<?php
$this->Html->addCrumb (__('Games', true));
$this->Html->addCrumb ("{$team['name']} vs {$opponent['name']}");
$this->Html->addCrumb (__('Live Game Scoring', true));
?>

<div class="games form">
<h2><?php  __('Live Game Scoring'); ?></h2>

<p>Submit live results for the <?php
echo $this->ZuluruTime->date ($game['GameSlot']['game_date']) . ' ' .
	$this->ZuluruTime->time ($game['GameSlot']['game_start']) . '-' .
	$this->ZuluruTime->time ($game['GameSlot']['display_game_end']);
?> at <?php echo $this->element('fields/block', array('field' => $game['GameSlot']['Field'], 'display_field' => 'long_name'));
?> between <?php echo $this->element('teams/block', array('team' => $team, 'show_shirt' => false));
?> and <?php echo $this->element('teams/block', array('team' => $opponent, 'show_shirt' => false)); ?>.</p>

<?php
if (!empty($game['ScoreEntry'])) {
	$entry = current($game['ScoreEntry']);
	if ($entry['team_id'] === null || $entry['team_id'] == $team['id']) {
		$team_score = $entry['score_for'];
		$opponent_score = $entry['score_against'];
	} else {
		$team_score = $entry['score_against'];
		$opponent_score = $entry['score_for'];
	}
} else {
	$team_score = $opponent_score = 0;
}
$has_stats = League::hasStats($game['Division']['League']);

$timeouts = Set::extract("/ScoreDetail[team_id={$team['id']}][play=Timeout]", $game);
echo $this->element('games/score_box', array('game' => $game, 'submitter' => $submitter, 'team' => $team, 'score' => $team_score, 'has_stats' => $has_stats, 'timeouts' => count($timeouts)));

$timeouts = Set::extract("/ScoreDetail[team_id={$opponent['id']}][play=Timeout]", $game);
echo $this->element('games/score_box', array('game' => $game, 'submitter' => $submitter, 'team' => $opponent, 'score' => $opponent_score, 'has_stats' => $has_stats, 'timeouts' => count($timeouts)));
?>
<div class="actions clear">
	<ul>
		<li><?php
		if (!$submitter) {
			echo $this->Html->link(__('Finalize', true), array('action' => 'edit', 'game' => $game['Game']['id'], 'stats' => $has_stats));
		} else {
			echo $this->Html->link(__('Finalize', true), array('action' => 'submit_score', 'game' => $game['Game']['id'], 'team' => $submitter));
		}
		?> </li>
	</ul>
</div>
<?php
if (Configure::read('feature.twitter')) {
	if (isset($entry)) {
		$twitter = "Score update #{$game['Division']['name']}: " . Game::twitterScore($team, $team_score, $opponent, $opponent_score);
	} else {
		$twitter = "#{$game['Division']['name']} game between " . Team::twitterName($team) . ' and ' . Team::twitterName($opponent) . " is about to start at {$game['GameSlot']['Field']['long_code']}.";
	}
	echo $this->Form->create('Twitter', array('url' => array('controller' => 'games', 'action' => 'tweet')));
	echo $this->Form->hidden('lat', array('value' => $game['GameSlot']['Field']['latitude']));
	echo $this->Form->hidden('long', array('value' => $game['GameSlot']['Field']['longitude']));
	echo $this->Form->input('message', array(
			'div' => 'clear',
			'cols' => 50,
			'rows' => 4,
			'value' => $twitter,
	));
	echo $this->Form->end('Tweet');

	echo $this->Html->scriptBlock ("
        jQuery(document).ready(function() {
            jQuery('#TwitterLiveScoreForm').ajaxForm({target: '#temp_update'});
        });
	");
}
?>
</div>

<?php if (empty($game['ScoreDetail'])): ?>
<div id="StartDetails<?php echo $team['id']; ?>" title="Game Start Details" class="form">
<div id="zuluru">
<?php
	$url = array('controller' => 'games', 'action' => 'play', 'game' => $game['Game']['id'], 'team' => $submitter);
	echo $this->Form->create(false, array(
		'id' => "StartForm{$team['id']}",
		'url' => $url,
	));

	// TODO: Make these sport-independent
	echo $this->Form->input('team_id', array(
			'label' => __('Pulling team', true),
			'options' => array(
				$team['id'] => $team['name'],
				$opponent['id'] => $opponent['name'],
			),
	));
	echo $this->Form->hidden('play', array('value' => 'Start'));
	echo $this->Form->end();
?>
<p class="warning-message">Do not click "Submit" until the game actually starts, as this initiates an internal timer used to track the times of plays.</p>
</div>
</div>
<?php
	echo $this->Html->scriptBlock ("
		jQuery('#StartDetails{$team['id']}').dialog({
			autoOpen: true,
			buttons: {
				'Submit': function() {
					jQuery(this).dialog('close');
					jQuery('#StartForm{$team['id']}').ajaxSubmit({
						type: 'POST',
						target: '#temp_update',
						error: function(message, status, error){
							alert('Error ' + status + ': ' + message.statusText);
						}
					});
					// Reset the form for the next time
					jQuery('#StartForm{$team['id']}').each(function(){
						this.reset();
					});
				}
			},
			modal: true,
			resizable: false,
			width: 500
		});
	");

endif;

$this->ZuluruHtml->script(array('jquery.form'), array('inline' => false));
?>
