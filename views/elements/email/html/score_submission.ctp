<p>Dear <?php echo implode(', ', Set::extract('/Person/first_name', $captains)); ?>,</p>
<p>Your opponent has indicated that the game between your team <?php
$url = Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $opponent['id']), true);
echo $this->Html->link($opponent['name'], $url);
?> and <?php
$url = Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']), true);
echo $this->Html->link($team['name'], $url);
?>, starting at <?php
$url = Router::url(array('controller' => 'games', 'action' => 'view', 'game' => $game['Game']['id']), true);
echo $this->Html->link($this->ZuluruTime->time($game['GameSlot']['game_start']), $url);
?> on <?php
echo $this->ZuluruTime->date($game['GameSlot']['game_date']);
?> in <?php echo $division['full_league_name']; ?> was <?php echo $opponent_status; ?>.</p>
<p>Scores need to be submitted in a timely fashion by both captains to substantiate results and for optimal scheduling of future games. We ask you to please submit the score as soon as possible. If the above score is correct, you can confirm it <?php
$url = Router::url(array_merge(array('controller' => 'games', 'action' => 'submit_score', 'game' => $game['Game']['id'], 'team' => $opponent['id']), compact('status', 'score_for', 'score_against')), true);
echo $this->Html->link('here', $url);
?>, otherwise you can submit your score <?php
$url = Router::url(array('controller' => 'games', 'action' => 'submit_score', 'game' => $game['Game']['id'], 'team' => $opponent['id']), true);
echo $this->Html->link('here', $url);
?>.</p>
<?php if ($division['finalize_after'] > 0): ?>
<p>Note that failure to report your score within <?php echo intval($division['finalize_after'] / 24); ?> days of your game will result in automatic score approval<?php
if (Configure::read('scoring.missing_score_spirit_penalty') > 0): ?>
 and a loss of <?php echo Configure::read('scoring.missing_score_spirit_penalty'); ?> Spirit points (not including Spirit points deducted by your opponent)<?php endif; ?>.</p>
<?php endif; ?>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
