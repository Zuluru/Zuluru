<?php
$this->Html->addCrumb (__('Teams', true));
$this->Html->addCrumb (sprintf(__('Starting with %s', true), $letter));
?>

<div class="teams index">
<h2><?php __('List Teams');?></h2>
<?php if (empty($teams)): ?>
<p class="warning-message">There are no teams currently running. Please check back periodically for updates.</p>
<?php else: ?>
<p><?php
__('Locate by letter: ');
$links = array();
foreach ($letters as $l) {
	$l = up($l[0]['letter']);
	if ($l != $letter) {
		$links[] = $this->Html->link($l, array('action' => 'letter', 'affiliate' => $affiliate, 'letter' => $l));
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
$affiliate_id = null;
foreach ($teams as $team):
	$is_manager = in_array($team['League']['affiliate_id'], $this->Session->read('Zuluru.ManagedAffiliateIDs'));
	Division::_addNames($team['Division'], $team['League']);

	if (count($affiliates) > 1 && $team['League']['affiliate_id'] != $affiliate_id):
		$affiliate_id = $team['League']['affiliate_id'];
?>
<tr>
	<th colspan="3">
		<h3 class="affiliate"><?php echo $team['Affiliate']['name']; ?></h3>
	</th>
</tr>
<?php
	endif;

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
			if ($is_logged_in && $team['Team']['open_roster'] && !Division::rosterDeadlinePassed($team['Division']) &&
				!in_array($team['Team']['id'], $this->Session->read('Zuluru.TeamIDs')))
			{
				echo $this->ZuluruHtml->iconLink('roster_add_24.png',
					array('action' => 'roster_request', 'team' => $team['Team']['id']),
					array('alt' => __('Join Team', true), 'title' => __('Join Team', true)));
			}
			if ($is_admin || $is_manager || in_array($team['Team']['id'], $this->Session->read('Zuluru.OwnedTeamIDs'))) {
				echo $this->ZuluruHtml->iconLink('edit_24.png',
					array('action' => 'edit', 'team' => $team['Team']['id'], 'return' => true),
					array('alt' => __('Edit Team', true), 'title' => __('Edit Team', true)));
				echo $this->ZuluruHtml->iconLink('email_24.png',
					array('action' => 'emails', 'team' => $team['Team']['id']),
					array('alt' => __('Player Emails', true), 'title' => __('Player Emails', true)));
			}
			if ($is_admin || $is_manager || (in_array($team['Team']['id'], $this->Session->read('Zuluru.OwnedTeamIDs')) && !Division::rosterDeadlinePassed($team['Division']))) {
				echo $this->ZuluruHtml->iconLink('roster_add_24.png',
					array('action' => 'add_player', 'team' => $team['Team']['id']),
					array('alt' => __('Add Player', true), 'title' => __('Add Player', true)));
			}
			if ($is_admin || $is_manager) {
				if (League::hasSpirit($team)) {
					echo $this->ZuluruHtml->iconLink('spirit_24.png',
						array('action' => 'spirit', 'team' => $team['Team']['id']),
						array('alt' => __('Spirit', true), 'title' => __('See Team Spirit Report', true)));
				}
				echo $this->ZuluruHtml->iconLink('move_24.png',
					array('action' => 'move', 'team' => $team['Team']['id'], 'return' => true),
					array('alt' => __('Move Team', true), 'title' => __('Move Team', true)));
				echo $this->ZuluruHtml->iconLink('delete_24.png',
					array('action' => 'delete', 'team' => $team['Team']['id'], 'return' => true),
					array('alt' => __('Delete', true), 'title' => __('Delete Team', true)),
					array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $team['Team']['id'])));
			}
			?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
</div>
