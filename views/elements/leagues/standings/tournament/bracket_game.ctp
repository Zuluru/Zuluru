<div class="game">
<?php if (!empty($game)): ?>
	<div class="home">
		<div class="team<?php
		if (Game::_is_finalized($game)) {
			if ($game['home_score'] > $game['away_score']) {
				echo ' winner';
			} else if ($game['home_score'] < $game['away_score']) {
				echo ' loser';
			}
		}
		?>">
<?php
if (!empty($game)) {
	if ($game['home_team'] !== null) {
		if ($game['home_dependency_type'] == 'seed') {
			echo "({$game['home_dependency_id']}) ";
		}
		echo $this->element('teams/block', array('team' => $teams[$game['home_team']], 'options' => array('max_length' => 16)));
	} else {
		Game::_readDependencies($game);
		echo $game['home_dependency'];
	}
?>
		</div>
		<div class="score">
<?php echo $game['home_score']; ?>
		</div>
	</div>
	<div class="details">
		<div class="name">
<?php
	if ($game['published'] || $is_admin || $is_coordinator) {
		echo $this->Html->link($game['name'], array('controller' => 'games', 'action' => 'view', 'game' => $game['id']));
	} else {
		echo $game['name'];
	}
?>
		</div>
<?php
	if (strtotime("{$game['GameSlot']['game_date']} {$game['GameSlot']['game_start']}") + Configure::read('timezone.adjust') * 60 > time()) {
		$date = $this->ZuluruTime->date ($game['GameSlot']['game_date']) . '<br/>' .
				$this->ZuluruTime->time ($game['GameSlot']['game_start']);
		if ($game['published']) {
			echo $this->Html->tag('div', $date, array('class' => 'date'));
		} else if ($is_admin || $is_coordinator) {
			echo $this->Html->tag('div', $date, array('class' => 'date unpublished'));
		}
	}
?>
	</div>
	<div class="away">
		<div class="team<?php
		if (Game::_is_finalized($game)) {
			if ($game['away_score'] > $game['home_score']) {
				echo ' winner';
			} else if ($game['away_score'] < $game['home_score']) {
				echo ' loser';
			}
		}
		?>">
<?php
	if ($game['away_team'] !== null) {
		if ($game['away_dependency_type'] == 'seed') {
			echo "({$game['away_dependency_id']}) ";
		}
		echo $this->element('teams/block', array('team' => $teams[$game['away_team']], 'options' => array('max_length' => 16)));
	} else {
		Game::_readDependencies($game);
		echo $game['away_dependency'];
	}
}
?>
		</div>
		<div class="score">
<?php echo $game['away_score']; ?>
		</div>
	</div>
<?php endif; ?>
</div>
