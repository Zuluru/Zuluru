League: <?php echo $game['Division']['League']['name']; ?>

Game: <?php echo $game['Game']['id']; ?>

Date: <?php echo $this->ZuluruTime->fulldate($game['GameSlot']['game_date']); ?>

Time: <?php echo $this->ZuluruTime->time($game['GameSlot']['game_start']); ?>

Home Team: <?php
echo $game['HomeTeam']['name'];
if ($game['HomeTeam']['id'] == $team_id) {
	echo ' (submitter)';
}
?>

<?php if (!empty($away_team)): ?>
Away Team: <?php
echo $game['AwayTeam']['name'];
if ($game['AwayTeam']['id'] == $team_id) {
	echo ' (submitter)';
}
?>

<?php endif; ?>
<?php echo Configure::read('sport.field_cap'); ?>: <?php echo $game['GameSlot']['Field']['long_name']; ?>


<?php echo $incident['details']; ?>
