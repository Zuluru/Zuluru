<div class="game">
<?php if (!empty($game)): ?>
	<div class="home">
		<div class="team<?php
		if (Game::_is_finalized($game)) {
			if ($game['Game']['home_score'] > $game['Game']['away_score']) {
				echo ' winner';
			} else if ($game['Game']['home_score'] < $game['Game']['away_score']) {
				echo ' loser';
			}
		}
		?>">
<?php
if (!empty($game)) {
	if ($game['Game']['home_team'] !== null) {
		if ($game['Game']['home_dependency_type'] == 'seed') {
			echo "({$game['Game']['home_dependency_id']}) ";
		}
		echo $this->element('team/block', array('team' => $teams[$game['Game']['home_team']], 'options' => array('max_length' => 16)));
	} else {
		$ths = ClassRegistry::init ('Game');
		switch ($game['Game']['home_dependency_type']) {
			case 'game_winner':
				$name = $ths->field('name', array('id' => $game['Game']['home_dependency_id']));
				printf (__('Winner of game %s', true), $name);
				break;

			case 'game_loser':
				$name = $ths->field('name', array('id' => $game['Game']['home_dependency_id']));
				printf (__('Loser of game %s', true), $name);
				break;

			case 'seed':
				printf (__('%s seed', true), ordinal($game['Game']['home_dependency_id']));
				break;
		}
	}
?>
		</div>
		<div class="score">
<?php echo $game['Game']['home_score']; ?>
		</div>
	</div>
	<div class="details">
		<div class="name">
<?php
	echo $game['Game']['name'];
?>
		</div>
<?php
	if (strtotime("{$game['GameSlot']['game_date']} {$game['GameSlot']['game_start']}") > time()) {
		$date = $this->ZuluruTime->date ($game['GameSlot']['game_date']) . '<br/>' .
				$this->ZuluruTime->time ($game['GameSlot']['game_start']);
		if ($game['Game']['published']) {
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
			if ($game['Game']['away_score'] > $game['Game']['home_score']) {
				echo ' winner';
			} else if ($game['Game']['away_score'] < $game['Game']['home_score']) {
				echo ' loser';
			}
		}
		?>">
<?php
	if ($game['Game']['away_team'] !== null) {
		if ($game['Game']['away_dependency_type'] == 'seed') {
			echo "({$game['Game']['away_dependency_id']}) ";
		}
		echo $this->element('team/block', array('team' => $teams[$game['Game']['away_team']], 'options' => array('max_length' => 16)));
	} else {
		$ths = ClassRegistry::init ('Game');
		switch ($game['Game']['away_dependency_type']) {
			case 'game_winner':
				$name = $ths->field('name', array('id' => $game['Game']['away_dependency_id']));
				printf (__('Winner of game %s', true), $name);
				break;

			case 'game_loser':
				$name = $ths->field('name', array('id' => $game['Game']['away_dependency_id']));
				printf (__('Loser of game %s', true), $name);
				break;

			case 'seed':
				printf (__('%s seed', true), ordinal($game['Game']['away_dependency_id']));
				break;
		}
	}
}
?>
		</div>
		<div class="score">
<?php echo $game['Game']['away_score']; ?>
		</div>
	</div>
<?php endif; ?>
</div>
