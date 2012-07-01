Dear <?php echo $captains; ?>,

You have not submitted a score for the game between your team <?php
echo $team['name']; ?> and <?php
echo $opponent['name']; ?>, starting at <?php
echo $this->ZuluruTime->time($game['GameSlot']['game_start']); ?> on <?php
echo $this->ZuluruTime->date($game['GameSlot']['game_date']);
?> in <?php echo $division['full_league_name']; ?>.

Scores need to be submitted in a timely fashion by both captains to substantiate results and for optimal scheduling of future games. Your opponent's submission for this game has now been accepted and they have been given a standard spirit score as a result of their timely submission.

<?php if (Configure::read('scoring.missing_score_spirit_penalty') > 0): ?>
Your team spirit score has been penalized due to your lack of submission - your opponent's Spirit score for your team minus <?php
echo Configure::read('scoring.missing_score_spirit_penalty'); ?> points. Overall team spirit can impact participation in future events.

If there is an exceptional reason why you were unable to submit your score in time, you may contact your coordinator who will consider reversing the penalty. To avoid such penalties in the future, please be sure to submit your scores promptly.

<?php endif; ?>
Thanks,
<?php echo Configure::read('email.admin_name'); ?>

<?php echo Configure::read('organization.short_name'); ?> web team
