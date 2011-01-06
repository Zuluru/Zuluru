<?php
$timezone = Configure::read('timezone.name');
$uid_prefix = 'G';
$game_id = $game['Game']['id'];
echo $this->element('game/ical', compact('game_id', 'team_id', 'game', 'timezone', 'uid_prefix'));
?>
