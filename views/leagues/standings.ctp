<?php
$this->Html->addCrumb (__('Leagues', true));
$this->Html->addCrumb ($league['League']['long_name']);
$this->Html->addCrumb (__('Standings', true));
?>

<div class="leagues standings">
<h2><?php  echo __('League Standings', true) . ': ' . $league['League']['long_name'];?></h2>
<?php
$season = Set::extract('/Game[tournament=0]/..', $league['Game']);
if (!empty($league['Team']) && !empty($season)):?>
	<table class="list">
	<?php
	echo $this->element("league/standings/{$league_obj->render_element}/heading",
			compact ('is_admin', 'is_coordinator', 'league'));
	$i = 0;
	if (isset ($more_before)) {
		$seed = $more_before;
		$classes = array();
		if (++$i % 2 == 0) {
			$classes[] = 'altrow';
		}
		echo $this->element("league/standings/{$league_obj->render_element}/more",
				compact ('league', 'teamid', 'classes'));
	} else {
		$seed = 0;
	}
	foreach ($league['Team'] as $team) {
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
		echo $this->element("league/standings/{$league_obj->render_element}/team",
				compact ('is_admin', 'is_coordinator', 'league', 'team', 'seed', 'classes'));
	}
	if (isset ($more_after)) {
		$classes = array();
		if (++$i % 2 == 0) {
			$classes[] = 'altrow';
		}
		echo $this->element("league/standings/{$league_obj->render_element}/more",
			compact ('league', 'teamid', 'classes'));
	}
	?>
	</table>
<?php endif; ?>
<?php
$tournament = Set::extract('/Game[tournament=1]/..', $league['Game']);
if (!empty($tournament)):
?>
<h3><?php __('Playoff brackets'); ?></h3>
<?php
echo $this->element('league/standings/tournament/bracket', array('games' => $tournament, 'teams' => $league['Team']));
?>
<?php endif; ?>
</div>
<div class="actions">
	<ul>
		<?php
		echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('view_32.png',
			array('action' => 'view', 'league' => $league['League']['id']),
			array('alt' => __('Details', true), 'title' => __('View League Details', true))));
		echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('schedule_32.png',
			array('action' => 'schedule', 'league' => $league['League']['id']),
			array('alt' => __('Schedule', true), 'title' => __('Schedule', true))));
		if ($is_admin || $is_coordinator) {
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('edit_32.png',
				array('action' => 'edit', 'league' => $league['League']['id']),
				array('alt' => __('Edit', true), 'title' => __('Edit League', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('email_32.png',
				array('action' => 'emails', 'league' => $league['League']['id']),
				array('alt' => __('Captain Emails', true), 'title' => __('Captain Emails', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('score_approve_32.png',
				array('action' => 'approve_scores', 'league' => $league['League']['id']),
				array('alt' => __('Approve scores', true), 'title' => __('Approve scores', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('schedule_add_32.png',
				array('controller' => 'schedules', 'action' => 'add', 'league' => $league['League']['id']),
				array('alt' => __('Add Games', true), 'title' => __('Add Games', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('spirit_32.png',
				array('action' => 'spirit', 'league' => $league['League']['id']),
				array('alt' => __('Spirit', true), 'title' => __('See League Spirit Report', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('field_report_32.png',
				array('action' => 'fields', 'league' => $league['League']['id']),
				array('alt' => __('Field Distribution', true), 'title' => __('Field Distribution Report', true))));
			// TODO: More links to reports, etc.
		}
		if ($is_admin) {
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('coordinator_add_32.png',
				array('action' => 'add_coordinator', 'league' => $league['League']['id']),
				array('alt' => __('Add Coordinator', true), 'title' => __('Add Coordinator', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('delete_32.png',
				array('action' => 'delete', 'league' => $league['League']['id']),
				array('alt' => __('Delete', true), 'title' => __('Delete League', true)),
				array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $league['League']['id']))));
		}
		?>
	</ul>
</div>
