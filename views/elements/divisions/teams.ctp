<div class="related">
	<?php if (!empty($teams)):?>
	<table class="list">
	<?php
	echo $this->element("leagues/view/{$league_obj->render_element}/heading", compact ('is_manager', 'division', 'league'));
	$seed = $i = 0;
	foreach ($teams as $team) {
		$is_captain = in_array($team['id'], $this->Session->read('Zuluru.OwnedTeamIDs'));
		$classes = array();
		if (floor ($seed++ / 8) % 2 == 1) {
			if (++$i % 2 == 1) {
				$classes[] = 'tier_alt_highlight';
			} else {
				$classes[] = 'tier_highlight';
			}
		} else {
			if (++$i % 2 == 1) {
				$classes[] = 'altrow';
			}
		}
		Team::consolidateRoster ($team);
		echo $this->element("leagues/view/{$league_obj->render_element}/team",
				compact('is_manager', 'team', 'division', 'league', 'seed', 'classes'));
	}
	?>
	</table>
	<?php endif; ?>
</div>
