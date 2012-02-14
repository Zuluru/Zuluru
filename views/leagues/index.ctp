<?php
$this->Html->addCrumb (__('Leagues', true));
$this->Html->addCrumb (__('List', true));
?>

<div class="leagues index">
<h2><?php __('Leagues');?></h2>
<table class="list">
<?php
$league = null;
foreach ($divisions as $division):
	if ($division['League']['id'] != $league):
		$league = $division['League']['id'];
?>
	<tr>
		<th<?php if (!$is_admin) echo ' colspan="2"'; ?>>
			<?php echo $this->Html->link($division['League']['full_name'], array('action' => 'view', 'league' => $division['League']['id'])); ?>
		</th>
		<?php if ($is_admin): ?>
		<th class="actions">
			<?php
				echo $this->ZuluruHtml->iconLink('edit_24.png',
					array('action' => 'edit', 'league' => $division['League']['id']),
					array('alt' => __('Edit', true), 'title' => __('Edit League', true)));
				echo $this->ZuluruHtml->iconLink('league_clone_24.png',
					array('controller' => 'leagues', 'action' => 'add', 'league' => $division['League']['id']),
					array('alt' => __('Clone League', true), 'title' => __('Clone League', true)));
				echo $this->ZuluruHtml->iconLink('division_add_24.png',
					array('controller' => 'divisions', 'action' => 'add', 'league' => $division['League']['id']),
					array('alt' => __('Add Division', true), 'title' => __('Add Division', true)));
				echo $this->ZuluruHtml->iconLink('delete_24.png',
					array('action' => 'delete', 'league' => $division['League']['id']),
					array('alt' => __('Delete', true), 'title' => __('Delete League', true)),
					array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $division['League']['id'])));
			?>
		</th>
		<?php endif; ?>
	</tr>
<?php
	endif;
?>
	<tr>
		<td>
			<?php echo $this->Html->link($division['Division']['name'], array('controller' => 'divisions', 'action' => 'view', 'division' => $division['Division']['id'])); ?>
		</td>
		<td class="actions">
			<?php
			echo $this->ZuluruHtml->iconLink('view_24.png',
				array('controller' => 'divisions', 'action' => 'view', 'division' => $division['Division']['id']),
				array('alt' => __('Details', true), 'title' => __('View Division Details', true)));
			echo $this->ZuluruHtml->iconLink('schedule_24.png',
				array('controller' => 'divisions', 'action' => 'schedule', 'division' => $division['Division']['id']),
				array('alt' => __('Schedule', true), 'title' => __('Schedule', true)));
			echo $this->ZuluruHtml->iconLink('standings_24.png',
				array('controller' => 'divisions', 'action' => 'standings', 'division' => $division['Division']['id']),
				array('alt' => __('Standings', true), 'title' => __('Standings', true)));
			if ($is_admin || in_array($division['Division']['id'], $this->Session->read('Zuluru.DivisionIDs'))) {
				echo $this->ZuluruHtml->iconLink('edit_24.png',
					array('controller' => 'divisions', 'action' => 'edit', 'division' => $division['Division']['id']),
					array('alt' => __('Edit', true), 'title' => __('Edit Division', true)));
				echo $this->ZuluruHtml->iconLink('email_24.png',
					array('controller' => 'divisions', 'action' => 'emails', 'division' => $division['Division']['id']),
					array('alt' => __('Captain Emails', true), 'title' => __('Captain Emails', true)));
				echo $this->ZuluruHtml->iconLink('score_approve_24.png',
					array('controller' => 'divisions', 'action' => 'approve_scores', 'division' => $division['Division']['id']),
					array('alt' => __('Approve scores', true), 'title' => __('Approve scores', true)));
				echo $this->ZuluruHtml->iconLink('schedule_add_24.png',
					array('controller' => 'divisions', 'controller' => 'schedules', 'action' => 'add', 'division' => $division['Division']['id']),
					array('alt' => __('Add Games', true), 'title' => __('Add Games', true)));
				if ($division['League']['numeric_sotg'] || $division['League']['sotg_questions'] != 'none') {
					echo $this->ZuluruHtml->iconLink('spirit_24.png',
						array('controller' => 'divisions', 'action' => 'spirit', 'division' => $division['Division']['id']),
						array('alt' => __('Spirit', true), 'title' => __('See Division Spirit Report', true)));
				}
				echo $this->ZuluruHtml->iconLink('field_report_24.png',
					array('controller' => 'divisions', 'action' => 'fields', 'division' => $division['Division']['id']),
					array('alt' => __('Field Distribution', true), 'title' => __('Field Distribution Report', true)));
			}
			if ($is_admin) {
				echo $this->ZuluruHtml->iconLink('coordinator_add_24.png',
					array('controller' => 'divisions', 'action' => 'add_coordinator', 'division' => $division['Division']['id']),
					array('alt' => __('Add Coordinator', true), 'title' => __('Add Coordinator', true)));
				echo $this->ZuluruHtml->iconLink('division_clone_24.png',
					array('controller' => 'divisions', 'action' => 'add', 'league' => $division['League']['id'], 'division' => $division['Division']['id']),
					array('alt' => __('Clone Division', true), 'title' => __('Clone Division', true)));
				if ($division['Division']['allstars'] != 'never') {
					echo $this->Html->link(__('Allstars', true), array('controller' => 'divisions', 'action' => 'allstars', 'division' => $division['Division']['id']));
				}
				echo $this->ZuluruHtml->iconLink('delete_24.png',
					array('controller' => 'divisions', 'action' => 'delete', 'division' => $division['Division']['id']),
					array('alt' => __('Delete', true), 'title' => __('Delete Division', true)),
					array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $division['Division']['id'])));
			}
			?>
		</td>
	</tr>
<?php endforeach; ?>
<?php if (isset($leagues)): ?>
<?php foreach ($leagues as $league): ?>
	<tr>
		<th<?php if (!$is_admin) echo ' colspan="2"'; ?>>
			<?php echo $this->Html->link($league['League']['full_name'], array('action' => 'view', 'league' => $league['League']['id'])); ?>
		</th>
		<?php if ($is_admin): ?>
		<th class="actions">
			<?php
				echo $this->ZuluruHtml->iconLink('edit_24.png',
					array('action' => 'edit', 'league' => $league['League']['id']),
					array('alt' => __('Edit', true), 'title' => __('Edit League', true)));
				echo $this->ZuluruHtml->iconLink('league_clone_24.png',
					array('controller' => 'leagues', 'action' => 'add', 'league' => $league['League']['id']),
					array('alt' => __('Clone League', true), 'title' => __('Clone League', true)));
				echo $this->ZuluruHtml->iconLink('division_add_24.png',
					array('controller' => 'divisions', 'action' => 'add', 'league' => $league['League']['id']),
					array('alt' => __('Add Division', true), 'title' => __('Add Division', true)));
				echo $this->ZuluruHtml->iconLink('delete_24.png',
					array('action' => 'delete', 'league' => $league['League']['id']),
					array('alt' => __('Delete', true), 'title' => __('Delete League', true)),
					array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $league['League']['id'])));
			?>
		</th>
		<?php endif; ?>
	</tr>
<?php
	endforeach;
endif;
?>
</table>
</div>
<?php if ($is_logged_in): ?>
<div class="actions">
	<ul>
<?php
foreach ($years as $year) {
	echo $this->Html->tag('li', $this->Html->link($year[0]['year'], array('year' => $year[0]['year'])));
}
?>

	</ul>
</div>
<?php endif; ?>