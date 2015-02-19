<?php
$this->Html->addCrumb (__('Daily Schedule', true));
$this->Html->addCrumb ($this->ZuluruTime->date($date));
?>

<div class="schedules day form">
<h2><?php echo __('Daily Schedule', true) . ': ' . $this->ZuluruTime->date($date); ?></h2>
<?php
echo $this->ZuluruForm->create(false, array('url' => Router::normalize($this->here), 'div' => false));
echo $this->ZuluruForm->input('date', array(
		'label' => false,
		'type' => 'date',
		'empty' => true,
		'default' => $date,
));
echo $this->ZuluruForm->end(__('Submit', true), array('div' => false));
?>
<?php if (empty($games)):?>
<p><?php __('No games scheduled for today.'); ?></p>
<?php else: ?>
	<table class="list">

<?php
// Check if we have any tournament games where we need to display the name.
$is_tournament = false;
foreach ($games as $game) {
	$is_tournament |= ($game['Game']['type'] != SEASON_GAME);
}

$sport = $last_slot = null;
foreach ($games as $game):
	if ($game['Division']['League']['sport'] != $sport):
		$sport = $game['Division']['League']['sport'];
		Configure::load("sport/$sport");
		if (count(Configure::read('options.sport')) > 1):
?>
<tr>
	<th colspan="6"><?php echo Inflector::humanize(__($sport, true)); ?></th>
</tr>

<?php
		endif;
?>
<tr>
	<th><?php if ($is_tournament): ?><?php __('Game'); ?><?php endif; ?></th>
	<th><?php __('Time'); ?></th>
	<th><?php __(Configure::read('sport.field_cap')); ?></th>
	<th><?php __('Home'); ?></th>
	<th><?php __('Away'); ?></th>
	<th><?php __('Score'); ?></th>
</tr>
<?php
	endif;

	// Are we a manager of this game?
	$is_game_manager = ($is_manager && in_array($game['Division']['League']['affiliate_id'], $this->UserCache->read('ManagedAffiliateIDs')));

	if (! ($game['Game']['published'] || $is_admin || $is_game_manager)) {
		continue;
	}
	if ($date != $game['GameSlot']['game_date']) {
		continue;
	}
	Game::_readDependencies($game);
	$same_slot = ($game['GameSlot']['id'] === $last_slot);
?>

<tr<?php if (!$game['Game']['published']) echo ' class="unpublished"'; ?>>
	<td><?php if ($is_tournament && !$same_slot): ?><?php echo $game['Game']['name']; ?><?php endif; ?></td>
	<td><?php
	if (!$same_slot) {
		$time = $this->ZuluruTime->time($game['GameSlot']['game_start']) . '-' .
				$this->ZuluruTime->time($game['GameSlot']['display_game_end']);
		echo $this->Html->link($time, array('controller' => 'games', 'action' => 'view', 'game' => $game['Game']['id']));
	}
	?></td>
	<td><?php if (!$same_slot) echo $this->element('fields/block', array('field' => $game['GameSlot']['Field'])); ?></td>
	<td><?php
	if (empty ($game['HomeTeam'])) {
		if (array_key_exists ('home_dependency', $game)) {
			echo $game['home_dependency'];
		} else {
			__('Unassigned');
		}
	} else {
		echo $this->element('teams/block', array('team' => $game['HomeTeam'], 'options' => array('max_length' => 16)));
	}
	?></td>
	<td><?php
	if (empty ($game['AwayTeam'])) {
		if (array_key_exists ('away_dependency', $game)) {
			echo $game['away_dependency'];
		} else {
			__('Unassigned');
		}
	} else {
		echo $this->element('teams/block', array('team' => $game['AwayTeam'], 'options' => array('max_length' => 16)));
	}
	?></td>
	<td class="actions"><?php echo $this->ZuluruGame->displayScore ($game, $game['Division'], $game['Division']['League']); ?></td>
</tr>

<?php
	$last_slot = $game['GameSlot']['id'];
endforeach;
?>

	</table>
<?php endif; ?>

</div>
<?php
echo $this->ZuluruHtml->script ('datepicker.js', array('inline' => false));
?>
