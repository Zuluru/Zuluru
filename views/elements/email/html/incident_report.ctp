<p>League: <?php echo $this->Html->link ($division['League']['name'],
	$this->Html->url (array('controller' => 'leagues', 'action' => 'view', 'league' => $division['League']['id']), true)); ?>
<br>Game: <?php echo $this->Html->link ($game['id'],
	$this->Html->url (array('controller' => 'games', 'action' => 'view', 'game' => $game['id']), true)); ?>
<br>Date: <?php echo $this->ZuluruTime->fulldate($slot['game_date']); ?>
<br>Time: <?php echo $this->ZuluruTime->time($slot['game_start']); ?>
<br>Home Team: <?php echo $this->Html->link ($home_team['name'],
	$this->Html->url (array('controller' => 'teams', 'action' => 'view', 'team' => $home_team['id']), true)); ?>
<?php if (!empty($away_team)): ?>
<br>Away Team: <?php echo $this->Html->link ($away_team['name'],
	$this->Html->url (array('controller' => 'teams', 'action' => 'view', 'team' => $away_team['id']), true)); ?>
<?php endif; ?>
<br>Field: <?php echo $field['long_name']; ?></p>
<p><?php echo $incident['details']; ?></p>
