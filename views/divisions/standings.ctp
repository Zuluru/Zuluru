<?php
$this->Html->addCrumb (__('Divisions', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('Standings', true));
?>

<div class="divisions standings">
<h2><?php  echo __('Division Standings', true) . ': ' . $division['Division']['full_league_name'];?></h2>
<?php
$season = Set::extract('/Game[tournament=0]/..', $division['Game']);
if (!empty($division['Team']) && !empty($season)):?>
	<table class="list">
	<?php
	echo $this->element("leagues/standings/{$league_obj->render_element}/heading",
			compact ('is_admin', 'is_coordinator', 'division'));
	$i = 0;
	if (isset ($more_before)) {
		$seed = $more_before;
		$classes = array();
		if (++$i % 2 == 0) {
			$classes[] = 'altrow';
		}
		echo $this->element("leagues/standings/{$league_obj->render_element}/more",
				compact ('division', 'teamid', 'classes'));
	} else {
		$seed = 0;
	}
	foreach ($division['Team'] as $team) {
		$classes = array();
		if (floor ($seed++ / 8) % 2 == 1) {
			if (++$i % 2 == 0) {
				$classes[] = 'tier_alt_highlight';
			} else {
				$classes[] = 'tier_highlight';
			}
		} else {
			if (++$i % 2 == 0) {
				$classes[] = 'altrow';
			}
		}
		if ($teamid == $team['id']) {
			$classes[] = 'team_highlight';
		}
		echo $this->element("leagues/standings/{$league_obj->render_element}/team",
				compact ('is_admin', 'is_coordinator', 'division', 'team', 'seed', 'classes'));
	}
	if (isset ($more_after)) {
		$classes = array();
		if (++$i % 2 == 0) {
			$classes[] = 'altrow';
		}
		echo $this->element("leagues/standings/{$league_obj->render_element}/more",
			compact ('division', 'teamid', 'classes'));
	}
	?>
	</table>
<?php endif; ?>
<?php
$tournament = Set::extract('/Game[tournament=1]/..', $division['Game']);
if (!empty($tournament)):
?>
<h3><?php __('Playoff brackets'); ?></h3>
<?php
echo $this->element('leagues/standings/tournament/bracket', array('games' => $tournament, 'teams' => $division['Team']));
?>
<?php endif; ?>
</div>
<div class="actions">
	<ul>
		<?php
		echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('view_32.png',
			array('action' => 'view', 'division' => $division['Division']['id']),
			array('alt' => __('Details', true), 'title' => __('View Division Details', true))));
		echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('schedule_32.png',
			array('action' => 'schedule', 'division' => $division['Division']['id']),
			array('alt' => __('Schedule', true), 'title' => __('Schedule', true))));
		if ($is_admin || $is_coordinator) {
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('edit_32.png',
				array('action' => 'edit', 'division' => $division['Division']['id']),
				array('alt' => __('Edit', true), 'title' => __('Edit Division', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('email_32.png',
				array('action' => 'emails', 'division' => $division['Division']['id']),
				array('alt' => __('Captain Emails', true), 'title' => __('Captain Emails', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('score_approve_32.png',
				array('action' => 'approve_scores', 'division' => $division['Division']['id']),
				array('alt' => __('Approve scores', true), 'title' => __('Approve scores', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('schedule_add_32.png',
				array('controller' => 'schedules', 'action' => 'add', 'division' => $division['Division']['id']),
				array('alt' => __('Add Games', true), 'title' => __('Add Games', true))));
			if (League::hasSpirit($division)) {
				echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('spirit_32.png',
					array('action' => 'spirit', 'division' => $division['Division']['id']),
					array('alt' => __('Spirit', true), 'title' => __('See Division Spirit Report', true))));
			}
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('field_report_32.png',
				array('action' => 'fields', 'division' => $division['Division']['id']),
				array('alt' => __('Field Distribution', true), 'title' => __('Field Distribution Report', true))));
			// TODO: More links to reports, etc.
		}
		if ($is_admin) {
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('coordinator_add_32.png',
				array('action' => 'add_coordinator', 'division' => $division['Division']['id']),
				array('alt' => __('Add Coordinator', true), 'title' => __('Add Coordinator', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('division_clone_32.png',
				array('action' => 'add', 'league' => $division['League']['id'], 'division' => $division['Division']['id']),
				array('alt' => __('Clone Division', true), 'title' => __('Clone Division', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('delete_32.png',
				array('action' => 'delete', 'division' => $division['Division']['id']),
				array('alt' => __('Delete', true), 'title' => __('Delete Division', true)),
				array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $division['Division']['id']))));
		}
		?>
	</ul>
</div>
