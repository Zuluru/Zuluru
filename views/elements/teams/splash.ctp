<?php if (!empty($teams) || $past_teams > 0): ?>
<table class="list">
<tr>
	<th colspan="2"><?php
	if (!empty($name)) {
		echo $name . ' ';
	}
	__('Teams');
	echo $this->ZuluruHtml->help(array('action' => 'teams', 'my_teams'));
	?></th>
</tr>
<?php
$i = 0;
foreach ($teams as $team):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td class="splash_item"><?php
		echo $this->element('teams/block', array('team' => $team['Team'])) .
				' (' . $this->element('divisions/block', array('division' => $team['Division'], 'field' => 'league_name')) . ')' .
				' (' . $this->element('people/roster_role', array('roster' => $team['TeamsPerson'], 'division' => $team['Division'])) . ')';
		if (!empty($team['Team']['division_id'])) {
			Configure::load("sport/{$team['Division']['League']['sport']}");
			$positions = Configure::read('sport.positions');
			if (!empty($positions)) {
				echo ' (' . $this->element('people/roster_position', array('roster' => $team['TeamsPerson'], 'division' => $team['Division'])) . ')';
			}
		}
		?></td>
		<td class="actions splash_action">
			<?php
			$is_captain = in_array($team['Team']['id'], $this->UserCache->read('OwnedTeamIDs'));
			echo $this->element('teams/actions', array('team' => $team['Team'], 'division' => $team['Division'], 'league' => $team['Division']['League'], 'is_captain' => $is_captain, 'format' => 'links'));
			?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
<?php if ($past_teams > 0): ?>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__('Show Team History', true), array('controller' => 'people', 'action' => 'teams', 'person' => $id)); ?> </li>
	</ul>
</div>
<div class="clear"></div>
<?php endif; ?>
<?php endif; ?>
