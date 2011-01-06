League: <?php echo $game['League']['name']; ?>

Game: <?php echo $game['Game']['id']; ?>

Date: <?php echo $this->ZuluruTime->fulldate($game['GameSlot']['game_date']); ?>

Time: <?php echo $this->ZuluruTime->time($game['GameSlot']['game_start']); ?>

Home Team: <?php echo $game['HomeTeam']['name']; ?>

Away Team: <?php echo $game['AwayTeam']['name']; ?>

Field: <?php echo "{$game['GameSlot']['Field']['code']} {$game['GameSlot']['Field']['num']}"; ?>


<?php echo $incident['details']; ?>
