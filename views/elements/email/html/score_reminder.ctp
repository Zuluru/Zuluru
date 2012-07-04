<p>Dear <?php echo $captains; ?>,</p>
<p>You have not yet submitted a score for the game between your team <?php
$url = Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']), true);
echo $this->Html->link($team['name'], $url);
?> and <?php
$url = Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $opponent['id']), true);
echo $this->Html->link($opponent['name'], $url);
?>, starting at <?php
$url = Router::url(array('controller' => 'games', 'action' => 'view', 'game' => $game['Game']['id']), true);
echo $this->Html->link($this->ZuluruTime->time($game['GameSlot']['game_start']), $url);
?> on <?php
echo $this->ZuluruTime->date($game['GameSlot']['game_date']);
?> in <?php echo $division['full_league_name']; ?>.</p>
<p>Scores need to be submitted in a timely fashion by both captains to substantiate results and for optimal scheduling of future games. We ask you to please submit the score as soon as possible. You can submit the score for this game <?php
$url = Router::url(array('controller' => 'games', 'action' => 'submit_score', 'game' => $game['Game']['id'], 'team' => $team['id']), true);
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
