<?php
$this->Html->addCrumb (__('Divisions', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('Standings', true));
?>

<div class="divisions standings">
<h2><?php  echo __('Division Standings', true) . ': ' . $division['Division']['full_league_name'];?></h2>
<?php
if (!empty($division['Team']) && !empty($division['Season'])):?>
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
	foreach ($show_teams as $team) {
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
if (!empty($division['Pool'])):
	echo $this->element('leagues/standings/tournament/notice');
?>
<h3><?php __('Preliminary rounds'); ?></h3>
<?php
	echo $this->element('leagues/standings/tournament/pools', array('games' => $division['Pool'], 'teams' => $division['Team']));
endif;

if (!empty($division['Bracket'])):
?>
<h3><?php __('Playoff brackets'); ?></h3>
<?php
	echo $this->element('leagues/standings/tournament/bracket', array('games' => $division['Bracket']['Game'], 'teams' => $division['Team']));
endif;
?>
</div>
<div class="actions"><?php echo $this->element('divisions/actions', array(
	'league' => $division['League'],
	'division' => $division['Division'],
	'format' => 'list',
)); ?></div>
