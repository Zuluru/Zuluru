Dear <?php echo $captains; ?>,

You have not yet submitted a score for the game between your team <?php
echo $team['name']; ?> and <?php
echo $opponent['name']; ?>, starting at <?php
echo $this->ZuluruTime->time($game['GameSlot']['game_start']); ?> on <?php
echo $this->ZuluruTime->date($game['GameSlot']['game_date']);
?> in <?php echo $division['full_league_name']; ?>.

Scores need to be submitted in a timely fashion by both captains to substantiate results and for optimal scheduling of future games. We ask you to please submit the score as soon as possible. You can submit the score for this game at <?php
echo Router::url(array('controller' => 'games', 'action' => 'submit_score', 'game' => $game['Game']['id'], 'team' => $team['id']), true); ?>


<?php if ($division['finalize_after'] > 0): ?>
Note that failure to report your score within <?php echo intval($division['finalize_after'] / 24); ?> days of your game will result in automatic score approval<?php
if (Configure::read('scoring.missing_score_spirit_penalty') > 0): ?>
 and a loss of <?php echo Configure::read('scoring.missing_score_spirit_penalty'); ?> Spirit points (not including Spirit points deducted by your opponent)<?php endif; ?>.

<?php endif; ?>
Thanks,
<?php echo Configure::read('email.admin_name'); ?>

<?php echo Configure::read('organization.short_name'); ?> web team
