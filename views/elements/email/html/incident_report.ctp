<p>League: <?php echo $this->Html->link ($game['Division']['League']['name'],
	$this->Html->url (array('controller' => 'leagues', 'action' => 'view', 'league' => $game['Division']['League']['id']), true)); ?>
<br>Game: <?php echo $this->Html->link ($game['Game']['id'],
	$this->Html->url (array('controller' => 'games', 'action' => 'view', 'game' => $game['Game']['id']), true)); ?>
<br>Date: <?php echo $this->ZuluruTime->fulldate($game['GameSlot']['game_date']); ?>
<br>Time: <?php echo $this->ZuluruTime->time($game['GameSlot']['game_start']); ?>
<br>Home Team: <?php echo $this->Html->link ($game['HomeTeam']['name'],
	$this->Html->url (array('controller' => 'teams', 'action' => 'view', 'team' => $game['HomeTeam']['id']), true)); ?>
<br>Away Team: <?php echo $this->Html->link ($game['AwayTeam']['name'],
	$this->Html->url (array('controller' => 'teams', 'action' => 'view', 'team' => $game['AwayTeam']['id']), true)); ?>
<br>Field: <?php echo $game['GameSlot']['Field']['long_name']; ?></p>
<p><?php echo $incident['details']; ?></p>
