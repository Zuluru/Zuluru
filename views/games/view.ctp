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
		<?php echo $this->Html->link($game['Division']['full_league_name'], array('controller' => 'divisions', 'action' => 'view', 'division' => $game['Division']['id'])); ?>

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
			echo ' (' . __('currently rated', true) . ": {$game['HomeTeam']['rating']})";
			if (!$preliminary && !Game::_is_finalized($game)) {
				printf (' (%0.1f%% %s)', $ratings_obj->calculateExpectedWin($game['HomeTeam']['rating'], $game['AwayTeam']['rating']) * 100, __('chance to win', true));
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
			echo ' (' . __('currently rated', true) . ": {$game['AwayTeam']['rating']})";
			if (!$preliminary && !Game::_is_finalized($game)) {
				printf (' (%0.1f%% %s)', $ratings_obj->calculateExpectedWin($game['AwayTeam']['rating'], $game['HomeTeam']['rating']) * 100, __('chance to win', true));
			}
		}
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
		<?php echo $this->element('fields/block', array('field' => $game['GameSlot']['Field'], 'display_field' => 'long_name')); ?>

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
?>

<fieldset class="clear wide_labels">
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
			<td><?php if (isset ($homeScoreEntry)) echo $homeScoreEntry['defaulted']; ?></td>
			<td><?php if (isset ($awayScoreEntry)) echo $awayScoreEntry['defaulted']; ?></td>
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
		</table>
		<?php endif; ?>
		<?php endif; ?>
	<?php endif; ?>
	</dl>
</fieldset>

<?php
echo $this->element ('spirit/view',
		array('team' => $game['HomeTeam'], 'league' => $game['Division']['League'], 'spirit' => $homeSpiritEntry, 'spirit_obj' => $spirit_obj));
echo $this->element ('spirit/view',
		array('team' => $game['AwayTeam'], 'league' => $game['Division']['League'], 'spirit' => $awaySpiritEntry, 'spirit_obj' => $spirit_obj));
?>

<?php if ($is_admin || $is_coordinator): ?>
	<?php if (Configure::read('scoring.allstars') && $game['Division']['allstars'] && !empty($game['Allstar'])):?>
	<fieldset>
 		<legend><?php __('Allstars'); ?></legend>
		<table class="list">
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