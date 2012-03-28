<p><?php echo $person['full_name']; ?> has added a note about the <?php
$url = Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $team['Team']['id']), true);
echo $this->Html->link($team['Team']['name'], $url);
?> game against <?php
$url = Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $opponent['id']), true);
echo $this->Html->link($opponent['name'], $url);
?> at <?php
$url = Router::url(array('controller' => 'fields', 'action' => 'view', 'field' => $game['GameSlot']['Field']['id']), true);
echo $this->Html->link($game['GameSlot']['Field']['long_name'], $url);
?> starting at <?php
echo $this->ZuluruTime->time($game['GameSlot']['game_start']); ?> on <?php
echo $this->ZuluruTime->date($game['GameSlot']['game_date']);
?>.</p>
<?php echo $this->data['Note']['note']; ?>
<p>To see all game details and notes, or add your own comment, see the <?php
$url = Router::url(array('controller' => 'games', 'action' => 'view', 'game' => $game['Game']['id']), true);
echo $this->Html->link('game details page', $url);
?>.</p>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
