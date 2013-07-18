<?php
// We need to sort pools using outside context; this class handles that.
if (!class_exists('PoolSorter')) {
	class PoolSorter {
		function compare($a, $b) {
			$team_a = $this->aliases[$a];
			$team_b = $this->aliases[$b];

			// If aliases haven't been resolved, sort by alias
			if (!$team_a || !$team_b) {
				preg_match('/\d+/', $a, $matches);
				$seed_a = $matches[0];
				preg_match('/\d+/', $b, $matches);
				$seed_b = $matches[0];
			} else {
				$seed_a = $this->seeds[$team_a];
				$seed_b = $this->seeds[$team_b];
			}

			if ($seed_a < $seed_b) {
				return -1;
			} else if ($seed_a > $seed_b) {
				return 1;
			}

			return 0;
		}
	}
}

ksort($games);
AppModel::_reindexOuter($teams, 'Team', 'id');

$pool_sorter = new PoolSorter();

foreach ($games as $stage => $stage_games):
	ksort($stage_games);
	foreach ($stage_games as $pool => $pool_games):
?>
<div class="pool_results">
<h4><?php
		printf(__('Pool %s', true), $pool_games['Game'][0]['HomePoolTeam']['Pool']['name']);
		if ($is_admin || $is_manager || $is_coordinator) {
			echo $this->ZuluruHtml->iconLink('delete_24.png',
				array('controller' => 'schedules', 'action' => 'delete', 'division' => $division['Division']['id'], 'pool' => $pool_games['Game'][0]['HomePoolTeam']['Pool']['id'], 'return' => true),
				array('alt' => __('Delete', true), 'title' => __('Delete pool games', true)));
			echo $this->ZuluruHtml->iconLink('initialize_24.png',
				array('action' => 'initialize_dependencies', 'division' => $division['Division']['id'], 'pool' => $pool_games['Game'][0]['HomePoolTeam']['Pool']['id'], 'return' => true),
				array('alt' => __('Initialize', true), 'title' => __('Initialize schedule dependencies', true)));
			echo $this->ZuluruHtml->iconLink('reset_24.png',
				array('action' => 'initialize_dependencies', 'division' => $division['Division']['id'], 'pool' => $pool_games['Game'][0]['HomePoolTeam']['Pool']['id'], 'reset' => true, 'return' => true),
				array('alt' => __('Reset', true), 'title' => __('Reset schedule dependencies', true)));
		}
?></h4>
<?php
		// Set the list of resolved aliases, but then remove anyone that doesn't have any results,
		// as the pre-determined sort order for them will be meaningless
		$aliases = $pool_sorter->aliases = Set::combine($pool_games, 'Game.{n}.HomePoolTeam.alias', 'Game.{n}.HomePoolTeam.team_id') + Set::combine($pool_games, 'Game.{n}.AwayPoolTeam.alias', 'Game.{n}.AwayPoolTeam.team_id');
		if (!empty($pool_games['Team'])) {
			$team_aliases = array_flip($pool_sorter->aliases);
			foreach ($pool_games['Team'] as $team) {
				if ($team['games'] == 0) {
					$pool_sorter->aliases[$team_aliases[$team['id']]] = null;
				}
			}
		}
		$pool_sorter->seeds = array_flip(Set::extract('/Team/id', $pool_games));

		$pool_aliases = Set::combine($pool_games, 'Game.{n}.HomePoolTeam.alias', 'Game.{n}.HomePoolTeam') + Set::combine($pool_games, 'Game.{n}.AwayPoolTeam.alias', 'Game.{n}.AwayPoolTeam');
		uksort($pool_aliases, array($pool_sorter, 'compare'));

		$pool_dates = array_unique(Set::extract('/Game/GameSlot/game_date', $pool_games));
		sort($pool_dates);
		if ($pool_dates[0] === null) {
			$pool_dates[0] = '';
		}
		$pool_dates = array_flip($pool_dates);
		foreach (array_keys($pool_dates) as $date) {
			if ($date == '') {
				// Assumption here is that any games without dates also have no times
				$pool_dates[$date] = array('');
			} else {
				$pool_dates[$date] = array_unique(Set::extract("/Game/GameSlot[game_date=$date]/game_start", $pool_games));
				sort($pool_dates[$date]);
			}
		}

		if ($is_admin || $is_coordinator) {
			$published = '';
		} else {
			$published = '[published=1]';
		}

		$time_games = array();
		$max_games = 0;
		foreach ($pool_dates as $date => $pool_times) {
			foreach ($pool_times as $time) {
				if (empty($time)) {
					// Assumption here is that the only games without times are ones with copy dependencies
					$time_games[$date][$time] = Set::extract("/Game[home_dependency_type=copy]$published", $pool_games);
				} else {
					$time_games[$date][$time] = Set::extract("/Game/GameSlot[game_date=$date][game_start=$time]$published/..", $pool_games);
				}
				$max_games = max($max_games, count($time_games[$date][$time]));
			}
		}
		$cols = 2 + $max_games * 2;
?>
<table class="list">
		<?php foreach ($pool_aliases as $alias => $pool): ?>
		<tr>
			<td colspan="<?php echo $cols; ?>"><?php
			if ($aliases[$alias] !== null) {
				$team = $aliases[$alias];
				echo '(';
				if (!empty($teams[$team]['Pool'][$stage])) {
					$results = current($teams[$team]['Pool'][$stage]);
					echo $results['W'] . '-' . $results['L'];
					if ($results['T']) {
						echo '-' . $results['T'];
					}
				} else {
					echo '0-0';
				}
				echo ') ' . $alias . ' ' . $this->element('teams/block', array('team' => $teams[$team], 'show_shirt' => false));
				if ($pool['Pool']['stage'] == 1) {
					echo ' (' . $teams[$team]['initial_seed'] . ')';
				}
			} else {
				$dependency = Pool::_dependency($pool);
				echo "(0-0) $alias [$dependency]";
			}
			?></td>
		</tr>
		<?php
		endforeach;

		if ($max_games > 0):
		?>
		<tr>
			<td><?php __('Day'); ?></td>
			<td><?php __('Time'); ?></td>
			<?php for ($i = 0; $i < $max_games; ++ $i): ?>
			<td><?php __('Game'); ?></td>
			<td><?php __('Score'); ?></td>
			<?php endfor; ?>
		</tr>
		<?php
		endif;

		$last_date = null;
		foreach ($pool_dates as $date => $pool_times):
			foreach ($pool_times as $time):
				if (!empty($time_games[$date][$time])):
		?>
		<tr>
			<td><?php
			if ($last_date != $date) {
				echo date('D', strtotime($date));
				$last_date = $date;
			}
			?></td>
			<td><?php echo $this->ZuluruTime->time($time); ?></td>
			<?php
					foreach ($time_games[$date][$time] as $game):
						if ($game['Game']['published']) {
							$class = '';
						} else if ($is_admin || $is_coordinator) {
							$class = ' class="unpublished"';
						}
			?>
			<td<?php echo $class; ?>><?php echo $this->Html->link($game['Game']['HomePoolTeam']['alias'] . 'v' . $game['Game']['AwayPoolTeam']['alias'], array('controller' => 'games', 'action' => 'view', 'game' => $game['Game']['id'])); ?></td>
			<td<?php echo $class; ?>><?php
						if (Game::_is_finalized($game)) {
							echo $game['Game']['home_score'] . '-' . $game['Game']['away_score'] . ' (F)';
						} else {
							$entry = Game::_get_best_score_entry($game['Game']);
							if ($entry) {
								if ($entry['team_id'] == $game['Game']['away_team']) {
									echo $entry['score_against'] . '-' . $entry['score_for'];
								} else {
									echo $entry['score_for'] . '-' . $entry['score_against'];
								}
							}
						}
			?></td>
			<?php endforeach; ?>
			<?php for ($i = count($time_games[$date][$time]); $i < $max_games; ++ $i): ?>
			<td></td>
			<td></td>
			<?php endfor; ?>
		</tr>
		<?php
				endif;
			endforeach;
		endforeach;
		?>
</table>
</div>
	<?php endforeach; ?>
<div class="clear"></div>
<?php endforeach; ?>
