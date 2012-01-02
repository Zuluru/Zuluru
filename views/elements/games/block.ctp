<?php
// Sometimes, there will be a 'Game' key, sometimes not
if (array_key_exists ('Game', $game)) {
	$game = array_merge ($game, $game['Game']);
	unset ($game['Game']);
}
$id = "game{$game['id']}";

if (isset ($options)) {
	$options = array_merge (array('class' => $id), $options);
} else {
	$options = array('class' => $id);
}

$display = $this->ZuluruTime->date($game['GameSlot']['game_date']) . ' ' .
			$this->ZuluruTime->time($game['GameSlot']['game_start']);
echo $this->ZuluruHtml->link($display,
	array('controller' => 'games', 'action' => 'view', 'game' => $game['id']),
	$options);

// Global variable. Ew.
global $game_blocks_shown;
if (!isset($game_blocks_shown)) {
	$game_blocks_shown = array();
}
if (!in_array($game['id'], $game_blocks_shown)) {
	$game_blocks_shown[] = $game['id'];
	$this->ZuluruHtml->buffer($this->element('games/tooltip', compact('game', 'id')));
	$this->Js->buffer("
$('.$id').tooltip({
	cancelDefault: false,
	delay: 1,
	predelay: 500,
	relative: true,
	tip: '#$id'
});
");
}
?>
