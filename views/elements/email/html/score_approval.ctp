<p>Dear <?php echo $captains; ?>,</p>
<p>You have not submitted a score for the game between your team <?php
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
<p>Scores need to be submitted in a timely fashion by both captains to substantiate results and for optimal scheduling of future games. Your opponent's submission for this game has now been accepted and they have been given a standard spirit score as a result of their timely submission.</p>
<?php if (Configure::read('scoring.missing_score_spirit_penalty') > 0): ?>
<p>Your team spirit score has been penalized due to your lack of submission - your opponent's Spirit score for your team minus <?php
echo Configure::read('scoring.missing_score_spirit_penalty'); ?> points. Overall team spirit can impact participation in future events.</p>
<p>If there is an exceptional reason why you were unable to submit your score in time, you may contact your coordinator who will consider reversing the penalty. To avoid such penalties in the future, please be sure to submit your scores promptly.</p>
<?php endif; ?>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
