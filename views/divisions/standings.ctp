<?php
$this->Html->addCrumb (__('Divisions', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('Standings', true));
?>

<?php if (!empty($division['Division']['header'])): ?>
<div class="division_header"><?php echo $division['Division']['header']; ?></div>
<?php endif; ?>
<div class="divisions standings">
<h2><?php  echo __('Division Standings', true) . ': ' . $division['Division']['full_league_name'];?></h2>
<?php
if (!empty($division['Team']) && !empty($division['Division']['Season'])):?>
	<table class="list">
	<?php
	echo $this->element("leagues/standings/{$league_obj->render_element}/heading", array(
			'is_admin' => $is_admin,
			'is_coordinator' => $is_coordinator,
			'league' => $division['League'],
			'division' => $division['Division'],
	));
	$i = 0;
	if (isset ($more_before)) {
		$seed = $more_before;
		$classes = array();
		if (++$i % 2 == 0) {
			$classes[] = 'altrow';
		}
		echo $this->element("leagues/standings/{$league_obj->render_element}/more", array(
				'league' => $division['League'],
				'division' => $division['Division'],
				'teamid' => $teamid,
				'classes' => $classes,
		));
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
		echo $this->element("leagues/standings/{$league_obj->render_element}/team", array(
				'is_admin' => $is_admin,
				'is_coordinator' => $is_coordinator,
				'league' => $division['League'],
				'division' => $division['Division'],
				'team' => $team,
				'seed' => $seed,
				'classes' => $classes,
		));
	}
	if (isset ($more_after)) {
		$classes = array();
		if (++$i % 2 == 0) {
			$classes[] = 'altrow';
		}
		echo $this->element("leagues/standings/{$league_obj->render_element}/more", array(
				'league' => $division['League'],
				'division' => $division['Division'],
				'teamid' => $teamid,
				'classes' => $classes,
		));
	}
	?>
	</table>
<?php
	if (League::hasSpirit($division['League'])) {
		echo $this->element('spirit/legend', compact('spirit_obj'));
	}
endif;
?>
<?php
if (!empty($division['Division']['Pools'])):
	echo $this->element('leagues/standings/tournament/notice');
?>
<h3><?php __('Preliminary rounds'); ?></h3>
<?php
	echo $this->element('leagues/standings/tournament/pools', array('division' => $division['Division'], 'games' => $division['Division']['Pools'], 'teams' => $division['Team']));
endif;

if (!empty($division['Division']['Bracket'])):
?>
<h3><?php __('Playoff brackets'); ?></h3>
<?php
	echo $this->element('leagues/standings/tournament/bracket', array('division' => $division['Division'], 'games' => $division['Division']['Bracket']['Game'], 'teams' => $division['Team']));
endif;
?>
</div>
<div class="actions"><?php echo $this->element('divisions/actions', array(
	'league' => $division['League'],
	'division' => $division['Division'],
	'format' => 'list',
)); ?></div>
<?php if (!empty($division['Division']['footer'])): ?>
<div class="division_footer"><?php echo $division['Division']['footer']; ?></div>
<?php endif; ?>
