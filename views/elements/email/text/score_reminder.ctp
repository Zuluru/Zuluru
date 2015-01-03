Dear <?php echo $captains; ?>,

You have not yet submitted a score for the game between your team <?php
echo $team['name']; ?> and <?php
echo $opponent['name']; ?>, starting at <?php
echo $this->ZuluruTime->time($game['GameSlot']['game_start']); ?> on <?php
echo $this->ZuluruTime->date($game['GameSlot']['game_date']);
?> in <?php echo $division['full_league_name']; ?>.

Scores need to be submitted in a timely fashion by both teams to substantiate results and for optimal scheduling of future games. We ask you to please submit the score as soon as possible. You can submit the score for this game at <?php
echo Router::url(array('controller' => 'games', 'action' => 'submit_score', 'game' => $game['Game']['id'], 'team' => $team['id']), true); ?>


<?php if ($division['finalize_after'] > 0): ?>
Remember to report the score within <?php
if ($division['finalize_after'] > 48) {
	echo intval($division['finalize_after'] / 24) . ' ' . __('days', true);
} else {
	echo $division['finalize_after'] . ' ' . __('hours', true);
}
?> of your game to avoid automatic score approval<?php
if (Configure::read('scoring.missing_score_spirit_penalty') > 0): ?>
 and a loss of <?php echo Configure::read('scoring.missing_score_spirit_penalty'); ?> Spirit points<?php endif; ?>.

<?php endif; ?>
<?php echo $this->element('email/text/footer'); ?>
