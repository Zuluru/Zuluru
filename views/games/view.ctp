<?php
$this->Html->addCrumb (__('Games', true));
$this->Html->addCrumb (__('Game', true) . ' ' . $game['Game']['id']);
$this->Html->addCrumb (__('View', true));
?>
<?php
$preliminary = ($game['Game']['home_team'] === null || $game['Game']['away_team'] === null);
?>

<div class="games view">
<h2><?php  __('View Game'); ?></h2>
<dl><?php $i = 0; $class = ' class="altrow"';?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('League', true) . '/' . __('Division', true); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $this->element('divisions/block', array('division' => $game['Division'], 'field' => 'full_league_name')); ?>

	</dd>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Home Team'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php
		if ($game['Game']['home_team'] === null) {
			echo $game['Game']['home_dependency'];
			$game['HomeTeam']['Person'] = array();
		} else {
			echo $this->element('teams/block', array('team' => $game['HomeTeam']));
			if (array_key_exists ('home_dependency', $game['Game'])) {
				echo " ({$game['Game']['home_dependency']})";
			}
			if ($game['Division']['schedule_type'] != 'tournament') {
				echo ' (' . __('currently rated', true) . ": {$game['HomeTeam']['rating']})";
				if (!$preliminary && !Game::_is_finalized($game)) {
					printf (' (%0.1f%% %s)', $ratings_obj->calculateExpectedWin($game['HomeTeam']['rating'], $game['AwayTeam']['rating']) * 100, __('chance to win', true));
				}
			}
		}
		?>

	</dd>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Away Team'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php
		if ($game['Game']['away_team'] === null) {
			echo $game['Game']['away_dependency'];
			$game['AwayTeam']['Person'] = array();
		} else {
			echo $this->element('teams/block', array('team' => $game['AwayTeam']));
			if (array_key_exists ('away_dependency', $game['Game'])) {
				echo " ({$game['Game']['away_dependency']})";
			}
			if ($game['Division']['schedule_type'] != 'tournament') {
				echo ' (' . __('currently rated', true) . ": {$game['AwayTeam']['rating']})";
				if (!$preliminary && !Game::_is_finalized($game)) {
					printf (' (%0.1f%% %s)', $ratings_obj->calculateExpectedWin($game['AwayTeam']['rating'], $game['HomeTeam']['rating']) * 100, __('chance to win', true));
				}
			}
		}
		?>

	</dd>
<?php if ($game['Game']['home_dependency_type'] != 'copy'): ?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Date and Time');?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php
		echo $this->ZuluruTime->date ($game['GameSlot']['game_date']) . ', ' .
			$this->ZuluruTime->time ($game['GameSlot']['game_start']) . '-' .
			$this->ZuluruTime->time ($game['GameSlot']['display_game_end']);
		?>
	</dd>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Location');?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $this->element('fields/block', array('field' => $game['GameSlot']['Field'], 'display_field' => 'long_name')); ?>

	</dd>
<?php endif; ?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Game Status'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php __(Inflector::humanize ($game['Game']['status'])); ?>

	</dd>
	<?php if ($game['Division']['schedule_type'] == 'round_robin' && $game['Game']['round']): ?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Round'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $game['Game']['round']; ?>

	</dd>
	<?php endif; ?>

	<?php
	if ($is_admin || $is_coordinator):
		$captains = array_merge ($game['HomeTeam']['Person'], $game['AwayTeam']['Person']);
		if (!empty ($captains)):
	?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Captain Emails'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $this->Html->link(__('Email all captains', true), 'mailto:' . implode (';', Set::extract ('/email_formatted', $captains))); ?>

	</dd>
	<?php
		endif;
	endif;
	?>

	<?php if (!$preliminary && $ratings_obj->per_game_ratings && !Game::_is_finalized($game) && $is_logged_in): ?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Ratings Table'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $this->Html->link(__('Click to view', true), array('action' => 'ratings_table', 'game' => $game['Game']['id'])); ?>

	</dd>
	<?php endif; ?>
</dl>

<?php
$my_teams = $this->Session->read('Zuluru.TeamIDs');
if (in_array($game['Game']['home_team'], $my_teams)) {
	$my_team = $game['HomeTeam'];
} else if (in_array($game['Game']['away_team'], $my_teams)) {
	$my_team = $game['AwayTeam'];
}
$display_attendance = isset($my_team) && $my_team['track_attendance'];
$can_annotate = Configure::read('feature.annotations') && isset($my_team);
?>
<?php if ($is_admin || $is_coordinator || $display_attendance || $can_annotate): ?>
<div class="actions">
	<ul>
		<?php if ($display_attendance): ?>
		<li><?php echo $this->ZuluruHtml->iconLink('attendance_24.png',
			array('controller' => 'games', 'action' => 'attendance', 'team' => $my_team['id'], 'game' => $game['Game']['id']),
			array('alt' => __('Attendance', true), 'title' => __('View Game Attendance Report', true))); ?></li>
		<?php endif; ?>
		<?php if ($can_annotate): ?>
		<li><?php echo $this->Html->link(__('Add Note', true), array('action' => 'note', 'game' => $game['Game']['id'])); ?> </li>
		<?php endif; ?>
		<?php if ($is_admin || $is_coordinator): ?>
		<li><?php echo $this->ZuluruHtml->iconLink('edit_24.png',
			array('action' => 'edit', 'game' => $game['Game']['id']),
			array('alt' => __('Edit Game', true), 'title' => __('Edit Game', true))); ?></li>
		<li><?php echo $this->ZuluruHtml->iconLink('delete_24.png',
			array('action' => 'delete', 'game' => $game['Game']['id']),
			array('alt' => __('Delete Game', true), 'title' => __('Delete Game', true)),
			array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $game['Game']['id']))); ?></li>
		<?php endif; ?>
		<?php if (Game::_is_finalized($game) && League::hasStats($game['Division']['League']) && ($is_logged_in || Configure::read('feature.public'))): ?>
		<li><?php echo $this->ZuluruHtml->iconLink('stats_24.png',
			array('action' => 'stats', 'game' => $game['Game']['id']),
			array('alt' => __('Game Stats', true), 'title' => __('Game Stats', true))); ?></li>
		<?php endif; ?>
	</ul>
</div>
<?php endif; ?>

<?php
$homeSpiritEntry = $awaySpiritEntry = null;
foreach ($game['SpiritEntry'] as $spiritEntry) {
	if ($spiritEntry['team_id'] == $game['Game']['home_team']) {
		$homeSpiritEntry = $spiritEntry;
	} else {
		$awaySpiritEntry = $spiritEntry;
	}
}
$team_names = array(
	$game['HomeTeam']['id'] => $game['HomeTeam']['name'],
	$game['AwayTeam']['id'] => $game['AwayTeam']['name']
);
?>

<fieldset class="clear wide_labels">
	<legend><?php __('Scoring'); ?></legend>
	<?php if (Game::_is_finalized($game)): ?>
	<dl>
		<?php if (!in_array($game['Game']['status'], Configure::read('unplayed_status'))): ?>
		<dt><?php echo $this->Text->truncate ($game['HomeTeam']['name'], 28); ?></dt>
		<dd>
			<?php echo $game['Game']['home_score']; ?>

		</dd>
		<dt><?php echo $this->Text->truncate ($game['AwayTeam']['name'], 28); ?></dt>
		<dd>
			<?php echo $game['Game']['away_score']; ?>

		</dd>
		<?php if (($is_admin || $is_coordinator || $game['Division']['League']['display_sotg'] != 'coordinator_only') &&
			League::hasSpirit($game)): ?>
		<dt><?php echo __('Spirit for', true) . ' ' . $this->Text->truncate ($game['HomeTeam']['name'], 18); ?></dt>
		<dd>
			<?php
			echo $this->element ('spirit/symbol', array(
					'spirit_obj' => $spirit_obj,
					'league' => $game['Division']['League'],
					'is_coordinator' => $is_coordinator,
					'entry' => $homeSpiritEntry,
			));
			?>
			&nbsp;
		</dd>
		<dt><?php echo __('Spirit for', true) . ' ' . $this->Text->truncate ($game['AwayTeam']['name'], 18); ?></dt>
		<dd>
			<?php
			echo $this->element ('spirit/symbol', array(
					'spirit_obj' => $spirit_obj,
					'league' => $game['Division']['League'],
					'is_coordinator' => $is_coordinator,
					'entry' => $awaySpiritEntry,
			));
			?>
			&nbsp;
		</dd>
		<?php endif; ?>
		<?php endif; ?>
		<?php
		if ($ratings_obj->per_game_ratings) {
			echo $this->element("leagues/game/{$league_obj->render_element}/score", compact('game'));
		}
		?>
		<dt><?php __('Score Approved By'); ?></dt>
		<dd>
			<?php
			if ($game['Game']['approved_by'] < 0) {
				$approved = Configure::read('approved_by');
				__($approved[$game['Game']['approved_by']]);
			} else {
				echo $this->element('people/block', array('person' => $game['ApprovedBy']));
			}
			?>

		</dd>
	</dl>
	<?php else: ?>
		<p><?php __('The score of this game has not yet been finalized.'); ?></p>
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
		<table class="list">
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
			<td><?php if (isset ($homeScoreEntry)) __($homeScoreEntry['status'] == 'home_default' ? 'us' : ($homeScoreEntry['status'] == 'away_default' ? 'them' : 'no')); ?></td>
			<td><?php if (isset ($awayScoreEntry)) __($awayScoreEntry['status'] == 'away_default' ? 'us' : ($awayScoreEntry['status'] == 'home_default' ? 'them' : 'no')); ?></td>
		</tr>
		<tr>
			<td><?php __('Entered By'); ?></td>
			<td><?php if (isset ($homeScoreEntry))
						echo $this->element('people/block', array('person' => $homeScoreEntry));
			?></td>
			<td><?php if (isset ($awayScoreEntry))
						echo $this->element('people/block', array('person' => $awayScoreEntry));
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
		<?php if (League::hasSpirit($game)): ?>
		<tr>
			<td><?php __('Spirit Assigned'); ?></td>
			<td><?php echo $this->element ('spirit/symbol', array(
					'spirit_obj' => $spirit_obj,
					'league' => $game['Division']['League'],
					'is_coordinator' => $is_coordinator,
					'entry' => $homeSpiritEntry,
			)); ?></td>
			<td><?php echo $this->element ('spirit/symbol', array(
					'spirit_obj' => $spirit_obj,
					'league' => $game['Division']['League'],
					'is_coordinator' => $is_coordinator,
					'entry' => $awaySpiritEntry,
			)); ?></td>
		</tr>
		<?php endif; ?>
		</table>
		<?php
		else:
			$entry = Game::_get_best_score_entry($game);
			if ($entry === null) {
				echo $this->Html->para(null, __('The final scores entered by the teams do not match, and the discrepancy has not been resolved.', true));
			}
		endif;
		?>
		<?php if (!empty($entry)): ?>
		<p><?php
		if ($entry['team_id'] === null) {
			$name = __('A scorekeeper', true);
		} else {
			$name = $team_names[$entry['team_id']];
		}
		if ($entry['status'] == 'in_progress') {
				printf (__('%s reported the following in-progress score as of %s:', true),
				$name, $this->ZuluruTime->time($entry['updated'])
			);
		} else {
			printf (__('%s reported the final score as:', true), $name);
		}
		?></p>
		<dl>
			<dt><?php echo $this->Text->truncate ($game['HomeTeam']['name'], 28); ?></dt>
			<dd>
				<?php echo ($entry['team_id'] != $game['AwayTeam']['id'] ? $entry['score_for'] : $entry['score_against']); ?>

			</dd>
			<dt><?php echo $this->Text->truncate ($game['AwayTeam']['name'], 28); ?></dt>
			<dd>
				<?php echo ($entry['team_id'] == $game['AwayTeam']['id'] ? $entry['score_for'] : $entry['score_against']); ?>

			</dd>
		</dl>
		<?php endif; ?>
	<?php endif; ?>

	<?php if (!empty($game['ScoreDetail'])): ?>
	<fieldset>
		<legend>Box Score</legend>
		<div id="BoxScore">
			<ul>
			<?php
			$start = strtotime("{$game['GameSlot']['game_date']} {$game['GameSlot']['game_start']}");
			$scores = array($game['HomeTeam']['id'] => 0, $game['AwayTeam']['id'] => 0);

			foreach ($game['ScoreDetail'] as $detail) {
				$time = strtotime($detail['created']) - $start;
				if ($detail['play'] == 'Start') {
					$start = strtotime($detail['created']);
					// TODO: Make this sport-independent
					$line = $this->ZuluruTime->time($start) . ' Game started, ' . $team_names[$detail['team_id']] . ' pulled';
				} else if (Configure::read("sport.other_options.{$detail['play']}")) {
					$line = sprintf("%d:%02d", $time / HOUR, ($time % HOUR) / MINUTE) . ' ' .
						$team_names[$detail['team_id']] . ' ' . low(Configure::read("sport.other_options.{$detail['play']}"));
				} else {
					$line = sprintf("%d:%02d", $time / HOUR, ($time % HOUR) / MINUTE) . ' ' .
						$team_names[$detail['team_id']] . ' ' .
						low($detail['play']);
					if ($detail['points']) {
						$scores[$detail['team_id']] += $detail['points'];
						$line .= ' (' . implode(' - ', $scores) . ')';
					}
					$stats = array();
					foreach ($detail['ScoreDetailStat'] as $stat) {
						$stats[] = Inflector::singularize(low($stat['StatType']['name'])) . ' ' . $stat['Person']['full_name'];
					}
					if (!empty($stats)) {
						$line .= ' (' . implode(', ', $stats) . ')';
					}
				}
				echo $this->Html->tag('li', $line);
			}
			?>
			</ul>
			<?php
			if ($is_admin || $is_coordinator) {
				echo $this->ZuluruHtml->iconLink('edit_24.png',
					array('action' => 'edit_boxscore', 'game' => $game['Game']['id']),
					array('alt' => __('Edit Box Score', true), 'title' => __('Edit Box Score', true)));
			}
			?>
		</div>
	</fieldset>
	<?php endif; ?>
</fieldset>

<?php if (!in_array($game['Game']['status'], Configure::read('unplayed_status'))): ?>

<?php
if (League::hasSpirit($game) &&
	($is_admin || $is_coordinator || ($homeSpiritEntry !== null && $awaySpiritEntry !== null)))
{
	echo $this->element ('spirit/view',
			array('team' => $game['HomeTeam'], 'league' => $game['Division']['League'], 'spirit' => $homeSpiritEntry, 'spirit_obj' => $spirit_obj));
	echo $this->element ('spirit/view',
			array('team' => $game['AwayTeam'], 'league' => $game['Division']['League'], 'spirit' => $awaySpiritEntry, 'spirit_obj' => $spirit_obj));
}
?>

<?php if ($is_admin || $is_coordinator): ?>
	<?php if (Configure::read('scoring.allstars') && $game['Division']['allstars'] && !empty($game['Allstar'])):?>
	<fieldset>
 		<legend><?php __('Allstars'); ?></legend>
		<table class="list">
		<tr>
			<th><?php __('Player'); ?></th>
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
				<td><?php echo $this->element('people/block', array('person' => $allstar)); ?></td>
			</tr>
		<?php endforeach; ?>
		</table>
	</fieldset>
	<?php endif; ?>

	<?php if (Configure::read('scoring.incident_reports') && !empty($game['Incident'])):?>
	<fieldset>
 		<legend><?php __('Incident Reports'); ?></legend>
		<table class="list">
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

<?php endif; ?>

<?php if (!empty($game['Note'])): ?>
	<fieldset>
 		<legend><?php __('Notes'); ?></legend>
		<table class="list">
		<tr>
			<th><?php __('From'); ?></th>
			<th><?php __('Note'); ?></th>
			<th><?php __('Visibility'); ?></th>
			<th class="actions"><?php __('Actions');?></th>
		</tr>
		<?php
			$i = 0;
			foreach ($game['Note'] as $note):
				$class = null;
				if ($i++ % 2 == 0) {
					$class = ' class="altrow"';
				}
			?>
			<tr<?php echo $class;?>>
				<td><?php
				echo $this->element('people/block', array('person' => $note['CreatedPerson']));
				echo $this->Html->tag('br');
				echo $this->ZuluruTime->datetime($note['created']); ?></td>
				<td><?php echo $note['note']; ?></td>
				<td><?php __(Configure::read("visibility.{$note['visibility']}")); ?></td>
				<td class="actions">
					<?php
					if ($note['created_person_id'] == $my_id) {
						echo $this->Html->link(__('Edit', true), array('action' => 'note', 'game' => $note['game_id'], 'note' => $note['id']));
						echo $this->Html->link(__('Delete', true), array('action' => 'delete_note', 'note' => $note['id']), null, __('Are you sure you want to delete this note?', true));
					}
					?>
				</td>
			</tr>
		<?php endforeach; ?>
		</table>
	</fieldset>
<?php endif; ?>

</div>
