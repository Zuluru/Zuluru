<?php
$this->Html->addCrumb (__('Leagues', true));
$this->Html->addCrumb ($league['League']['full_name']);
$this->Html->addCrumb (__('Standings', true));
?>

<div class="leagues standings">
<h2><?php  echo __('League Standings', true) . ': ' . $league['League']['full_name'];?></h2>
<?php foreach ($league['Division'] as $division): ?>
	<?php if (!empty($division['header']) && !empty($division['Game'])): ?>
	<div class="division_header"><?php echo $division['header']; ?></div>
	<?php endif; ?>
	<?php if (!empty($division['Team']) && !empty($division['Season'])):?>
		<?php if (count($league['Division']) > 1 && !empty($division['name'])): ?>
		<h3><?php echo $division['name']; ?></h3>
		<?php endif; ?>
		<table class="list">
		<?php
		echo $this->element("leagues/standings/{$division['render_element']}/heading", array(
				'is_admin' => $is_admin,
				'is_coordinator' => $is_coordinator,
				'league' => $league['League'],
				'division' => $division,
		));
		$i = 0;
		$seed = 0;
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
			echo $this->element("leagues/standings/{$division['render_element']}/team", array(
					'is_admin' => $is_admin,
					'is_coordinator' => $is_coordinator,
					'league' => $league['League'],
					'division' => $division,
					'team' => $team,
					'seed' => $seed,
					'classes' => $classes,
			));
		}
		?>
		</table>
	<?php endif; ?>
	<?php
	if (!empty($division['Pools'])):
		echo $this->element('leagues/standings/tournament/notice');
	?>
	<h4><?php __('Preliminary rounds'); ?></h4>
	<?php
		echo $this->element('leagues/standings/tournament/pools', array('division' => $division, 'games' => $division['Pools'], 'teams' => $division['Team']));
	endif;

	if (!empty($division['Bracket'])):
	?>
	<h4><?php __('Playoff brackets'); ?></h4>
	<?php
		echo $this->element('leagues/standings/tournament/bracket', array('division' => $division, 'games' => $division['Bracket']['Game'], 'teams' => $division['Team']));
	endif;
	?>
	<?php if (!empty($division['footer']) && !empty($division['Game'])): ?>
	<div class="division_footer"><?php echo $division['footer']; ?></div>
	<?php endif; ?>
<?php endforeach; ?>
</div>

<div class="actions"><?php echo $this->element('leagues/actions', array(
		'league' => $league,
		'format' => 'list',
)); ?></div>