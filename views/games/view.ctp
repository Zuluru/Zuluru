<?php
$this->Html->addCrumb (__('Games', true));
$this->Html->addCrumb (__('Game', true) . ' ' . $game['Game']['id']);
$this->Html->addCrumb (__('View', true));
?>

<div class="games view">
<h2><?php  __('View Game'); ?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('League', true) . '/' . __('Division', true); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($game['League']['long_name'], array('controller' => 'leagues', 'action' => 'view', 'league' => $game['League']['id'])); ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Home Team'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php
			echo $this->Html->link($game['HomeTeam']['name'], array('controller' => 'teams', 'action' => 'view', 'team' => $game['HomeTeam']['id'])) .
					' (' . __('rated', true) . ': ' . $game['HomeTeam']['rating'] . ') ';
			if (!Game::_is_finalized($game))
				printf ('(%0.1f%% %s)', Game::_calculate_expected_win($game['HomeTeam']['rating'], $game['AwayTeam']['rating']) * 100, __('chance to win', true));
			?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Away Team'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php
			echo $this->Html->link($game['AwayTeam']['name'], array('controller' => 'teams', 'action' => 'view', 'team' => $game['AwayTeam']['id'])) .
					' (' . __('rated', true) . ': ' . $game['AwayTeam']['rating'] . ') ';
			if (!Game::_is_finalized($game))
				printf ('(%0.1f%% %s)', Game::_calculate_expected_win($game['AwayTeam']['rating'], $game['HomeTeam']['rating']) * 100, __('chance to win', true));
			?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Date and Time');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php
			echo $this->ZuluruTime->date ($game['GameSlot']['game_date']) . ', ' .
				$this->ZuluruTime->time ($game['GameSlot']['game_start']) . '-' .
				$this->ZuluruTime->time ($game['GameSlot']['display_game_end']);
			?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Location');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link("{$game['GameSlot']['Field']['code']} {$game['GameSlot']['Field']['num']}",
					array('controller' => 'fields', 'action' => 'view', 'field' => $game['GameSlot']['field_id'])); ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Game Status'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __(Inflector::humanize ($game['Game']['status'])); ?>

		</dd>
		<?php if ($game['Game']['round']): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Round'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $game['Game']['round']; ?>

		</dd>
		<?php endif; ?>

		<?php if ($is_admin || $is_coordinator):?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Captain Emails'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php
			$captains = array_merge ($game['HomeTeam']['Person'], $game['AwayTeam']['Person']);
			echo $this->Html->link(__('Email all captains', true), 'mailto:' . implode (';', Set::extract ('/email_formatted', $captains)));
			?>

		</dd>
		<?php endif; ?>

		<?php if (!Game::_is_finalized($game) && $is_logged_in): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Ratings Table'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link(__('Click to view', true), array('action' => 'ratings_table', 'game' => $game['Game']['id'])); ?>

		</dd>
		<?php endif; ?>
	</dl>

	<?php
	$homeSpiritEntry = $awaySpiritEntry = null;
	foreach ($game['SpiritEntry'] as $spiritEntry) {
		if ($spiritEntry['team_id'] == $game['Game']['home_team']) {
			$homeSpiritEntry = $spiritEntry;
		} else {
			$awaySpiritEntry = $spiritEntry;
		}
	}
	?>

	<fieldset class="wide_labels">
 		<legend><?php __('Scoring'); ?></legend>
		<dl>
		<?php if (Game::_is_finalized($game)): ?>
			<dt><?php echo $this->Text->truncate ($game['HomeTeam']['name'], 28); ?></dt>
			<dd>
				<?php echo $game['Game']['home_score']; ?>

			</dd>
			<dt><?php echo $this->Text->truncate ($game['AwayTeam']['name'], 28); ?></dt>
			<dd>
				<?php echo $game['Game']['away_score']; ?>

			</dd>
			<?php if ($is_admin || $is_coordinator || $game['League']['display_sotg'] != 'coordinator_only'): ?>
			<dt><?php echo __('Spirit for', true) . ' ' . $this->Text->truncate ($game['HomeTeam']['name'], 18); ?></dt>
			<dd>
				<?php
				echo $this->element ('spirit/symbol', array(
						'spirit_obj' => $spirit_obj,
						'type' => $game['League']['display_sotg'],
						'is_coordinator' => $is_coordinator,
						'value' => $homeSpiritEntry['entered_sotg'],
				));
				?>
				&nbsp;
			</dd>
			<dt><?php echo __('Spirit for', true) . ' ' . $this->Text->truncate ($game['AwayTeam']['name'], 18); ?></dt>
			<dd>
				<?php
				echo $this->element ('spirit/symbol', array(
						'spirit_obj' => $spirit_obj,
						'type' => $game['League']['display_sotg'],
						'is_coordinator' => $is_coordinator,
						'value' => $awaySpiritEntry['entered_sotg'],
				));
				?>
				&nbsp;
			</dd>
			<?php endif; ?>
			<?php echo $this->element("league/game/{$league_obj->render_element}/score", compact('game')); ?>
			<dt><?php __('Score Approved By'); ?></dt>
			<dd>
				<?php
				if ($game['Game']['approved_by'] < 0) {
					$approved = Configure::read('approved_by');
					__($approved[$game['Game']['approved_by']]);
				} else {
					echo $this->Html->link($game['ApprovedBy']['full_name'],
							array('controller' => 'people', 'action' => 'view', 'person' => $game['ApprovedBy']['id']));
				}
				?>

			</dd>
		<?php else: ?>
			<p><?php __('Score not yet finalized'); ?></p>
			<?php if (!empty($game['ScoreEntry']) && ($is_admin || $is_coordinator)):?>
			<h3><?php __('Score as entered'); ?></h3>
			<?php
			if (array_key_exists ($game['Game']['home_team'], $game['ScoreEntry'])) {
				$homeScoreEntry = $game['ScoreEntry'][$game['Game']['home_team']];
			}
			if (array_key_exists ($game['Game']['away_team'], $game['ScoreEntry'])) {
				$awayScoreEntry = $game['ScoreEntry'][$game['Game']['away_team']];
			}
			?>
			<table>
			<tr>
				<th></th>
				<th><?php echo $this->Text->truncate ($game['HomeTeam']['name'], 23) . ' (' . __('home', true) . ')'; ?></th>
				<th><?php echo $this->Text->truncate ($game['AwayTeam']['name'], 23) . ' (' . __('away', true) . ')'; ?></th>
			</tr>
			<tr>
				<td><?php __('Home Score'); ?></td>
				<td><?php if (isset ($homeScoreEntry)) echo $homeScoreEntry['score_for']; else echo __('not entered'); ?></td>
				<td><?php if (isset ($awayScoreEntry)) echo $awayScoreEntry['score_against']; else echo __('not entered'); ?></td>
			</tr>
			<tr>
				<td><?php __('Away Score'); ?></td>
				<td><?php if (isset ($homeScoreEntry)) echo $homeScoreEntry['score_against']; else echo __('not entered'); ?></td>
				<td><?php if (isset ($awayScoreEntry)) echo $awayScoreEntry['score_for']; else echo __('not entered'); ?></td>
			</tr>
			<tr>
				<td><?php __('Defaulted?'); ?></td>
				<td><?php if (isset ($homeScoreEntry)) echo $homeScoreEntry['defaulted']; ?></td>
				<td><?php if (isset ($awayScoreEntry)) echo $awayScoreEntry['defaulted']; ?></td>
			</tr>
			<tr>
				<td><?php __('Entered By'); ?></td>
				<td><?php if (isset ($homeScoreEntry))
							echo $this->Html->link($homeScoreEntry['Person']['full_name'],
								array('controller' => 'people', 'action' => 'view', 'person' => $homeScoreEntry['person_id']));
				?></td>
				<td><?php if (isset ($awayScoreEntry))
							echo $this->Html->link($awayScoreEntry['Person']['full_name'],
								array('controller' => 'people', 'action' => 'view', 'person' => $awayScoreEntry['person_id']));
				?></td>
			</tr>
			<tr>
				<td><?php __('Entry Time'); ?></td>
				<td><?php
				if (isset ($homeScoreEntry)) {
					echo $this->ZuluruTime->datetime($homeScoreEntry['created']);
				}
				?></td>
				<td><?php
				if (isset ($awayScoreEntry)) {
					echo $this->ZuluruTime->datetime($awayScoreEntry['created']);
				}
				?></td>
			</tr>
			<?php if ($game['League']['numeric_sotg'] || $game['League']['sotg_questions'] != 'none'): ?>
			<tr>
				<td><?php __('Spirit Assigned'); ?></td>
				<td><?php if ($homeSpiritEntry != null) echo $homeSpiritEntry['entered_sotg']; ?></td>
				<td><?php if ($awaySpiritEntry != null) echo $awaySpiritEntry['entered_sotg']; ?></td>
			</tr>
			</table>
			<?php endif; ?>
			<?php endif; ?>
		<?php endif; ?>
		</dl>
	</fieldset>

	<?php
	echo $this->element ('spirit/view',
			array('team' => $game['HomeTeam'], 'league' => $game['League'], 'spirit' => $homeSpiritEntry, 'spirit_obj' => $spirit_obj));
	echo $this->element ('spirit/view',
			array('team' => $game['AwayTeam'], 'league' => $game['League'], 'spirit' => $awaySpiritEntry, 'spirit_obj' => $spirit_obj));
	?>

<?php if ($is_admin || $is_coordinator): ?>
	<?php if (Configure::read('scoring.allstars') && $game['League']['allstars'] && !empty($game['Allstar'])):?>
	<fieldset>
 		<legend><?php __('Allstars'); ?></legend>
		<table cellpadding = "0" cellspacing = "0">
		<tr>
			<th><?php __('Player'); ?></th>
			<th class="actions"><?php __('Actions');?></th>
		</tr>
		<?php
			$i = 0;
			foreach ($game['Allstar'] as $allstar):
				$class = null;
				if ($i++ % 2 == 0) {
					$class = ' class="altrow"';
				}
			?>
			<tr<?php echo $class;?>>
				<td><?php echo $allstar['Person']['full_name']; ?></td>
				<td class="actions">
					<?php echo $this->Html->link(__('Delete', true), array('controller' => 'allstars', 'action' => 'delete', 'person' => $allstar['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $allstar['id'])); ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</table>
	</fieldset>
	<?php endif; ?>

	<?php if (Configure::read('scoring.incident_reports') && !empty($game['Incident'])):?>
	<fieldset>
 		<legend><?php __('Incident Reports'); ?></legend>
		<table cellpadding = "0" cellspacing = "0">
		<tr>
			<th><?php __('Reporting Team'); ?></th>
			<th><?php __('Type'); ?></th>
			<th><?php __('Details'); ?></th>
		</tr>
		<?php foreach ($game['Incident'] as $incident): ?>
			<tr>
				<td><?php
				if ($game['HomeTeam']['id'] == $incident['team_id']) {
					echo $game['HomeTeam']['name'];
				} else {
					echo $game['AwayTeam']['name'];
				}
				?></td>
				<td><?php echo $incident['type'];?></td>
				<td><?php echo $incident['details'];?></td>
			</tr>
		<?php endforeach; ?>
		</table>
	</fieldset>
	<?php endif; ?>
<?php endif; ?>

</div>
<?php if ($is_admin || $is_coordinator): ?>
	<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__('Edit Game', true), array('action' => 'edit', 'game' => $game['Game']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('Delete Game', true), array('action' => 'delete', 'game' => $game['Game']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $game['Game']['id'])); ?> </li>
		<?php if (Configure::read('scoring.allstars') && $game['League']['allstars']): ?>
		<li><?php echo $this->Html->link(__('Add Allstar', true), array('controller' => 'allstars', 'action' => 'add', 'game' => $game['Game']['id']));?> </li>
		<?php endif; ?>
	</ul>
</div>
<?php endif; ?>
