<?php
$this->Html->addCrumb (__('Leagues', true));
$this->Html->addCrumb ($league['League']['long_name']);
$this->Html->addCrumb (__('Standings', true));
?>

<div class="leagues standings">
<h2><?php  echo __('League Standings', true) . ': ' . $league['League']['long_name'];?></h2>
<?php if (!empty($league['Team'])):?>
	<table>
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
</div>
<div class="actions">
	<ul>
		<?php
		echo $this->Html->tag ('li', $this->Html->link(__('Details', true), array('action' => 'view', 'league' => $league['League']['id'])));
		echo $this->Html->tag ('li', $this->Html->link(__('Schedule', true), array('action' => 'schedule', 'league' => $league['League']['id'])));
		if ($is_admin || $is_coordinator) {
			echo $this->Html->tag ('li', $this->Html->link(__('Edit League', true), array('action' => 'edit', 'league' => $league['League']['id'])));
			echo $this->Html->tag ('li', $this->Html->link(__('Captain Emails', true), array('action' => 'emails', 'league' => $league['League']['id'])));
			echo $this->Html->tag ('li', $this->Html->link(__('Approve scores', true), array('action' => 'approve_scores', 'league' => $league['League']['id'])));
			echo $this->Html->tag ('li', $this->Html->link(__('Add games', true), array('controller' => 'schedules', 'action' => 'add', 'league' => $league['League']['id'])));
			// TODO: More links to reports, etc.
		}
		if ($is_admin) {
			echo $this->Html->tag ('li', $this->Html->link(__('Delete League', true), array('action' => 'delete', 'league' => $league['League']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $league['League']['id'])));
			echo $this->Html->tag ('li', $this->Html->link(__('Spirit', true), array('action' => 'spirit', 'league' => $league['League']['id'])));
		}
		?>
	</ul>
</div>
