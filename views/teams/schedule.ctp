<?php
$this->Html->addCrumb (__('Teams', true));
$this->Html->addCrumb ($team['Team']['name']);
$this->Html->addCrumb (__('Schedule', true));
?>

<?php
$display_spirit = $is_admin || $is_coordinator || $team['League']['display_sotg'] != 'coordinator_only';
?>
<div class="teams schedule">
<h2><?php  echo __('Team Schedule', true) . ': ' . $team['Team']['name'];?></h2>
<?php if (!empty($team['Game'])):?>
	<table>
		<tr>
			<th><?php __('Date'); ?></th>
			<th><?php __('Time'); ?></th>
			<th><?php __('Field'); ?></th>
			<th><?php __('Opponent'); ?></th>
			<th><?php __('Score'); ?></th>
			<?php if ($display_spirit): ?>
			<th><?php __('Spirit'); ?></th>
			<?php endif; ?>
		</tr>
	<?php
	$i = 0;
	foreach ($team['Game'] as $game):
		if (! ($game['Game']['published'] || $is_admin || $is_coordinator)) {
			continue;
		}
		$classes = array();
		if ($i++ % 2 == 0) {
			$classes[] = 'altrow';
		}
		if (!$game['Game']['published']) {
			$classes[] = 'unpublished';
		}
		Game::_adjustEntryIndices ($game);
		if (array_key_exists ($team['Team']['id'], $game['SpiritEntry'])) {
			$value = $game['SpiritEntry'][$team['Team']['id']]['entered_sotg'];
		} else {
			$value = null;
		}
	?>
		<tr<?php if (!empty ($classes)) echo ' class="' . implode (' ', $classes) . '"'; ?>>
			<td><?php echo $this->ZuluruTime->fulldate($game['GameSlot']['game_date']); ?></td>
			<td><?php
			$time = $this->ZuluruTime->time($game['GameSlot']['game_start']) . '-' .
					$this->ZuluruTime->time($game['GameSlot']['display_game_end']);
			echo $this->Html->link($time, array('controller' => 'games', 'action' => 'view', 'game' => $game['Game']['id']));
			?></td>
			<td><?php echo $this->Html->link("{$game['GameSlot']['Field']['code']} {$game['GameSlot']['Field']['num']}",
					array('controller' => 'fields', 'action' => 'view', 'field' => $game['GameSlot']['Field']['id']), array('title' => "{$game['GameSlot']['Field']['name']} {$game['GameSlot']['Field']['num']}")); ?></td>
			<td><?php
			if ($team['Team']['id'] == $game['Game']['home_team']) {
				echo $this->Html->link($game['AwayTeam']['name'], array('controller' => 'teams', 'action' => 'view', 'team' => $game['Game']['away_team'])) . ' ' .
					$this->element('shirt', array('colour' => $game['AwayTeam']['shirt_colour']));
			} else {
				echo $this->Html->link($game['HomeTeam']['name'], array('controller' => 'teams', 'action' => 'view', 'team' => $game['Game']['home_team'])) . ' ' .
					$this->element('shirt', array('colour' => $game['HomeTeam']['shirt_colour']));
			}
			?></td>
			<td><?php echo $this->ZuluruGame->displayScore ($game, $team['Team']['id']); ?></td>
			<?php if ($display_spirit): ?>
			<td><?php echo $this->element ('spirit/symbol', array(
					'spirit_obj' => $spirit_obj,
					'type' => $team['League']['display_sotg'],
					'is_coordinator' => $is_coordinator,
					'value' => $value,
			)); ?></td>
			<?php endif; ?>
		</tr>
	<?php
	endforeach;
	?>
	</table>
<?php endif; ?>
<p>Get your team schedule in <?php // TODO: Better image locations, alt text
echo $this->ZuluruHtml->imageLink ('/img/ical.gif', array('action' => 'ical', $team['Team']['id'], 'team.ics'), array('alt' => 'iCal'));
?> format or <?php
echo $this->ZuluruHtml->imageLink ('http://www.google.com/calendar/images/ext/gc_button6.gif', 'http://www.google.com/calendar/render?cid=' . $this->Html->url(array('action' => 'ical', $team['Team']['id']), true), array('alt' => 'add to Google Calendar'), array('target' => '_blank'));
?>.</p>
</div>
