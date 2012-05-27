<?php
$this->Html->addCrumb (__('Teams', true));
$this->Html->addCrumb (sprintf(__('Starting with %s', true), $letter));
?>

<div class="teams index">
<h2><?php __('List Teams');?></h2>
<p><?php
__('Locate by letter: ');
$links = array();
foreach ($letters as $l) {
	$l = up($l[0]['letter']);
	if ($l != $letter) {
		$links[] = $this->Html->link($l, array('action' => 'letter', 'letter' => $l));
	} else {
		$links[] = $letter;
	}
}
echo implode ('&nbsp;&nbsp;', $links);
?></p>
<table class="list">
<tr>
	<th><?php __('Name');?></th>
	<th><?php __('Division');?></th>
	<th class="actions"><?php __('Actions');?></th>
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
		<td>
			<?php echo $this->element('teams/block', array('team' => $team['Team'])); ?>
		</td>
		<td>
			<?php echo $this->Html->link($team['Division']['full_league_name'], array('controller' => 'divisions', 'action' => 'view', 'division' => $team['Division']['id'])); ?>
		</td>
		<td class="actions">
			<?php
			echo $this->ZuluruHtml->iconLink('schedule_24.png',
				array('action' => 'schedule', 'team' => $team['Team']['id']),
				array('alt' => __('Schedule', true), 'title' => __('Schedule', true)));
			echo $this->ZuluruHtml->iconLink('standings_24.png',
				array('controller' => 'divisions', 'action' => 'standings', 'division' => $team['Division']['id'], 'team' => $team['Team']['id']),
				array('alt' => __('Standings', true), 'title' => __('Standings', true)));
			if ($is_logged_in && $team['Team']['open_roster'] && $team['Division']['roster_deadline'] >= date('Y-m-d') &&
				!in_array($team['Team']['id'], $this->Session->read('Zuluru.TeamIDs')))
			{
				echo $this->ZuluruHtml->iconLink('roster_add_32.png',
					array('action' => 'roster_request', 'team' => $team['Team']['id']),
					array('alt' => __('Join Team', true), 'title' => __('Join Team', true)));
			}
			if ($is_admin || in_array($team['Team']['id'], $this->Session->read('Zuluru.OwnedTeamIDs'))) {
				echo $this->ZuluruHtml->iconLink('edit_24.png',
					array('action' => 'edit', 'team' => $team['Team']['id']),
					array('alt' => __('Edit Team', true), 'title' => __('Edit Team', true)));
				echo $this->ZuluruHtml->iconLink('email_24.png',
					array('action' => 'emails', 'team' => $team['Team']['id']),
					array('alt' => __('Player Emails', true), 'title' => __('Player Emails', true)));
			}
			if ($is_admin || (in_array($team['Team']['id'], $this->Session->read('Zuluru.OwnedTeamIDs')) && $team['Division']['roster_deadline'] >= date('Y-m-d'))) {
				echo $this->ZuluruHtml->iconLink('roster_add_24.png',
					array('action' => 'add_player', 'team' => $team['Team']['id']),
					array('alt' => __('Add Player', true), 'title' => __('Add Player', true)));
			}
			if ($is_admin) {
				if (League::hasSpirit($team)) {
					echo $this->ZuluruHtml->iconLink('spirit_24.png',
						array('action' => 'spirit', 'team' => $team['Team']['id']),
						array('alt' => __('Spirit', true), 'title' => __('See Team Spirit Report', true)));
				}
				echo $this->ZuluruHtml->iconLink('move_24.png',
					array('action' => 'move', 'team' => $team['Team']['id']),
					array('alt' => __('Move Team', true), 'title' => __('Move Team', true)));
				echo $this->ZuluruHtml->iconLink('delete_24.png',
					array('action' => 'delete', 'team' => $team['Team']['id']),
					array('alt' => __('Delete', true), 'title' => __('Delete Team', true)),
					array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $team['Team']['id'])));
			}
			?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
</div>
