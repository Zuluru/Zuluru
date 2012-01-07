<p>Game <?php echo $game['Game']['id']; ?>
 between <?php echo $game['HomeTeam']['name']; ?>
 and <?php echo $game['AwayTeam']['name']; ?>
 in <?php echo $game['Division']['League']['name']; ?>
 has score entries which do not match. Edit the game <?php
echo $this->Html->link('here', $this->Html->url(array('controller' => 'games', 'action' => 'edit', 'game' => $game['Game']['id']), true));
?>.</p>
