<?php
$this->Html->addCrumb (__('Leagues', true));
$this->Html->addCrumb (__('List', true));
?>

<div class="leagues index">
<h2><?php __('Leagues');?></h2>
<table cellpadding="0" cellspacing="0">
<tr>
	<th><?php echo __('Name');?></th>
	<th class="actions"><?php __('Actions');?></th>
</tr>
<?php
$i = 0;
foreach ($leagues as $league):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $this->Html->link($league['League']['long_name'], array('action' => 'view', 'league' => $league['League']['id'])); ?>
		</td>
		<td class="actions">
			<?php
			echo $this->ZuluruHtml->iconLink('view_32.png',
				array('action' => 'view', 'league' => $league['League']['id']),
				array('alt' => __('Details', true), 'title' => __('View League Details', true)));
			echo $this->ZuluruHtml->iconLink('schedule_32.png',
				array('action' => 'schedule', 'league' => $league['League']['id']),
				array('alt' => __('Schedule', true), 'title' => __('Schedule', true)));
			echo $this->ZuluruHtml->iconLink('standings_32.png',
				array('action' => 'standings', 'league' => $league['League']['id']),
				array('alt' => __('Standings', true), 'title' => __('Standings', true)));
			if ($is_admin || in_array($league['League']['id'], $this->Session->read('Zuluru.LeagueIDs'))) {
				echo $this->ZuluruHtml->iconLink('edit_32.png',
					array('action' => 'edit', 'league' => $league['League']['id']),
					array('alt' => __('Edit', true), 'title' => __('Edit League', true)));
				echo $this->ZuluruHtml->iconLink('email_32.png',
					array('action' => 'emails', 'league' => $league['League']['id']),
					array('alt' => __('Captain Emails', true), 'title' => __('Captain Emails', true)));
				echo $this->ZuluruHtml->iconLink('score_approve_32.png',
					array('action' => 'approve_scores', 'league' => $league['League']['id']),
					array('alt' => __('Approve scores', true), 'title' => __('Approve scores', true)));
				echo $this->ZuluruHtml->iconLink('schedule_add_32.png',
					array('controller' => 'schedules', 'action' => 'add', 'league' => $league['League']['id']),
					array('alt' => __('Add Games', true), 'title' => __('Add Games', true)));
				echo $this->ZuluruHtml->iconLink('spirit_32.png',
					array('action' => 'spirit', 'league' => $league['League']['id']),
					array('alt' => __('Spirit', true), 'title' => __('See League Spirit Report', true)));
			}
			if ($is_admin) {
				echo $this->ZuluruHtml->iconLink('coordinator_add_32.png',
					array('action' => 'add_coordinator', 'league' => $league['League']['id']),
					array('alt' => __('Add Coordinator', true), 'title' => __('Add Coordinator', true)));
				if ($league['League']['allstars'] != 'never') {
					echo $this->Html->link(__('Allstars', true), array('action' => 'allstars', 'league' => $league['League']['id']));
				}
				echo $this->ZuluruHtml->iconLink('delete_32.png',
					array('action' => 'delete', 'league' => $league['League']['id']),
					array('alt' => __('Delete', true), 'title' => __('Delete League', true)),
					array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $league['League']['id'])));
			}
			?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
</div>
<div class="actions">
	<ul>
<?php
foreach ($years as $year) {
	echo $this->Html->tag('li', $this->Html->link($year[0]['year'], array('year' => $year[0]['year'])));
}
?>

	</ul>
</div>
