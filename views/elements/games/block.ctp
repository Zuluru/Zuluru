<?php
// Sometimes, there will be a 'Game' key, sometimes not
if (array_key_exists ('Game', $game)) {
	$game = array_merge ($game, $game['Game']);
	unset ($game['Game']);
}
$id = "games_game_{$game['id']}";

if (isset ($options)) {
	$options = array_merge (array('id' => $id, 'class' => 'trigger'), $options);
} else {
	$options = array('id' => $id, 'class' => 'trigger');
}

$display = $this->ZuluruTime->date($game['GameSlot']['game_date']) . ' ' .
			$this->ZuluruTime->time($game['GameSlot']['game_start']);
echo $this->ZuluruHtml->link($display,
	array('controller' => 'games', 'action' => 'view', 'game' => $game['id']),
	$options);

echo $this->element('tooltips');
?>
