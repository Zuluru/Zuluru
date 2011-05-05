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
?>
<div id="<?php echo $id; ?>" class="tooltip">
<h2><?php printf (__('Game %d', true), $game['id']); ?></h2>
<dl>
	<dt><?php __('Date'); ?></dt>
	<dd><?php echo $this->ZuluruTime->date($game['GameSlot']['game_date']); ?></dd>
	<dt><?php __('Time'); ?></dt>
	<dd><?php echo $this->ZuluruTime->time($game['GameSlot']['game_start']); ?> - <?php echo $this->ZuluruTime->time($game['GameSlot']['display_game_end']); ?></dd>
	<dt><?php __('Field'); ?></dt>
	<dd><?php echo $this->Html->link($game['GameSlot']['Field']['long_name'],
			array('controller' => 'fields', 'action' => 'view', 'field' => $game['GameSlot']['Field']['id'])); ?></dd>
	<dt><?php __('Home Team'); ?></dt>
	<dd><?php
	echo $this->Html->link($game['HomeTeam']['name'],
			array('controller' => 'teams', 'action' => 'view', 'team' => $game['HomeTeam']['id']));
	if (array_key_exists ('shirt_colour', $game['HomeTeam'])) {
		echo ' ' . $this->element('shirt', array('colour' => $game['HomeTeam']['shirt_colour']));
	}
	?></dd>
	<dt><?php __('Away Team'); ?></dt>
	<dd><?php
	echo $this->Html->link($game['AwayTeam']['name'],
			array('controller' => 'teams', 'action' => 'view', 'team' => $game['AwayTeam']['id']));
	if (array_key_exists ('shirt_colour', $game['AwayTeam'])) {
		echo ' ' . $this->element('shirt', array('colour' => $game['AwayTeam']['shirt_colour']));
	}
	?></dd>

</dl>
</div>
<?php
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
