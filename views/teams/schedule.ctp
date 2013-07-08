<?php
$this->Html->addCrumb (__('Teams', true));
$this->Html->addCrumb ($team['Team']['name']);
$this->Html->addCrumb (__('Schedule', true));
?>

<?php
$display_spirit = ($is_admin || $is_coordinator || $team['Division']['League']['display_sotg'] != 'coordinator_only') &&
	League::hasSpirit($team);
?>
<div class="teams schedule">
<h2><?php  echo __('Team Schedule', true) . ': ' . $team['Team']['name'];?></h2>
<?php if (!empty($team['Game'])):?>
	<table class="list">
		<tr>
			<th><?php __('Date'); ?></th>
			<th><?php __('Time'); ?></th>
			<th><?php __(Configure::read('sport.field_cap')); ?></th>
			<th><?php __('Opponent'); ?></th>
			<th><?php __('Score'); ?></th>
			<?php if ($display_spirit): ?>
			<th><?php __('Spirit'); ?></th>
			<?php endif; ?>
			<?php if ($display_attendance): ?>
			<th><?php __('Attendance'); ?></th>
			<?php endif; ?>
			<?php if ($annotate): ?>
			<th><?php __('Notes'); ?></th>
			<?php endif; ?>
		</tr>
	<?php
	$i = 0;
	foreach ($team['Game'] as $game):
		$is_event = array_key_exists('TeamEvent', $game);

		if (!$is_event && !($game['Game']['published'] || $is_admin || $is_coordinator)) {
			continue;
		}
		$classes = array();
		if ($i++ % 2 == 0) {
			$classes[] = 'altrow';
		}
		if ($is_event) {
			$date = $game['TeamEvent']['date'];
			$start = $game['TeamEvent']['start'];
			$end = $game['TeamEvent']['end'];
		} else {
			if (!$game['Game']['published']) {
				$classes[] = 'unpublished';
			}
			Game::_adjustEntryIndices ($game);
			Game::_readDependencies($game);
			if ($display_spirit && !in_array($game['Game']['status'], Configure::read('unplayed_status')) &&
				Game::_is_finalized($game) && array_key_exists ($team['Team']['id'], $game['SpiritEntry']))
			{
				$entry = $game['SpiritEntry'][$team['Team']['id']];
			} else {
				$entry = null;
			}

			$date = $game['GameSlot']['game_date'];
			$start = $game['GameSlot']['game_start'];
			$end = $game['GameSlot']['display_game_end'];
		}
	?>
		<tr<?php if (!empty ($classes)) echo ' class="' . implode (' ', $classes) . '"'; ?>>
			<td><?php echo $this->ZuluruTime->fulldate($date); ?></td>
			<td><?php
			$time = $this->ZuluruTime->time($start) . '-' .
					$this->ZuluruTime->time($end);
			if ($is_event) {
				echo $this->Html->link($time, array('controller' => 'team_events', 'action' => 'view', 'event' => $game['TeamEvent']['id']));
			} else {
				echo $this->Html->link($time, array('controller' => 'games', 'action' => 'view', 'game' => $game['Game']['id']));
			}
			?></td>
			<td><?php
			if ($is_event) {
				$address = "{$game['TeamEvent']['location_street']}, {$game['TeamEvent']['location_city']}, {$game['TeamEvent']['location_province']}";
				$link_address = strtr ($address, ' ', '+');
				echo $this->Html->link($game['TeamEvent']['location_name'], "http://maps.google.com/maps?q=$link_address");
			} else {
				echo $this->element('fields/block', array('field' => $game['GameSlot']['Field']));
			}
			?></td>
			<td><?php
			if ($is_event) {
				echo $this->Html->link($game['TeamEvent']['name'], array('controller' => 'team_events', 'action' => 'view', 'event' => $game['TeamEvent']['id']));
			} else {
				if ($team['Team']['id'] == $game['Game']['home_team']) {
					if ($game['Game']['away_team'] === null) {
						echo $game['Game']['away_dependency'];
					} else {
						echo $this->element('teams/block', array('team' => $game['AwayTeam']));
					}
				} else {
					if ($game['Game']['home_team'] === null) {
						echo $game['Game']['home_dependency'];
					} else {
						echo $this->element('teams/block', array('team' => $game['HomeTeam']));
					}
				}
			}
			?></td>
			<td class="actions"><?php
			if (!$is_event) {
				echo $this->ZuluruGame->displayScore ($game, $team['Division']['League'], $team['Team']['id']);
			}
			?></td>
			<?php if ($display_spirit): ?>
			<td><?php
				if (!$is_event) {
					echo $this->element ('spirit/symbol', array(
							'spirit_obj' => $spirit_obj,
							'league' => $team['Division']['League'],
							'is_coordinator' => $is_coordinator,
							'entry' => $entry,
					));
				}
			?></td>
			<?php endif; ?>
			<?php if ($display_attendance): ?>
			<td class="actions"><?php
			if ($is_event) {
				echo $this->Html->link(__('View', true), array('controller' => 'team_events', 'action' => 'view', 'event' => $game['TeamEvent']['id']));
			} else {
				echo $this->Html->link(__('View', true), array('controller' => 'games', 'action' => 'attendance', 'team' => $team['Team']['id'], 'game' => $game['Game']['id']));
			}
			$counts = array();
			// TODO: Handle team event attendance counts
			foreach (array('Male', 'Female') as $gender) {
				$count = count(Set::extract("/Person[gender=$gender]", $game['Attendance']));
				if ($count) {
					$counts[] = $count . substr (__($gender, true), 0, 1);
				}
			}
			echo implode (' / ', $counts);
			?></td>
			<?php endif; ?>
			<?php if ($annotate): ?>
			<td class="actions"><?php
			if (!$is_event) {
				echo $this->Html->link(__('Add', true), array('controller' => 'games', 'action' => 'note', 'game' => $game['Game']['id']));
			}
			?></td>
			<?php endif; ?>
		</tr>
	<?php
	endforeach;
	?>
	</table>
<?php endif; ?>
<p>Get your team schedule in <?php // TODO: Better image locations, alt text
echo $this->ZuluruHtml->iconLink ('ical.gif', array('action' => 'ical', $team['Team']['id'], 'team.ics'), array('alt' => 'iCal'));
?> format or <?php
echo $this->ZuluruHtml->imageLink ('http://www.google.com/calendar/images/ext/gc_button6.gif', 'http://www.google.com/calendar/render?cid=' . $this->Html->url(array('action' => 'ical', $team['Team']['id']), true), array('alt' => 'add to Google Calendar'), array('target' => 'google'));
?>.</p>
</div>

<div class="actions">
	<?php echo $this->element('teams/actions', array('team' => $team['Team'], 'division' => $team['Division'], 'league' => $team['Division']['League'], 'format' => 'list')); ?>
</div>
