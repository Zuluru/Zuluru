<?php
$this->Html->addCrumb (__('Divisions', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('Schedule', true));
?>

<?php
// Perhaps remove manager status, if we're looking at a different affiliate
if ($is_manager && !in_array($division['League']['affiliate_id'], $this->UserCache->read('ManagedAffiliateIDs'))) {
	$is_manager = false;
}
?>

<?php if (!empty($division['Division']['header'])): ?>
<div class="division_header"><?php echo $division['Division']['header']; ?></div>
<?php endif; ?>
<div class="divisions schedule">
<h2><?php echo __('Division Schedule', true) . ': ' . $division['Division']['full_league_name'];?></h2>
<?php
if ($division['Division']['schedule_type'] == 'tournament') {
	echo $this->element('leagues/schedule/tournament/notice');
}

if (!empty ($edit_date)) {
	echo $this->Form->create ('Game', array('url' => Router::normalize($this->here)));

	// Put the slots into a more useful form for us
	$slots = array();
	$game_slots = reset($game_slots);
	usort($game_slots, array('GameSlot', 'compareTimeAndField'));
	foreach ($game_slots as $slot) {
		if ($is_tournament || $multi_day) {
			$slots[$slot['GameSlot']['id']] = $this->ZuluruTime->day ($slot['GameSlot']['game_date']) . ' ' . $this->ZuluruTime->time ($slot['GameSlot']['game_start']) . ' ' . $slot['Field']['long_name'];
		} else {
			$slots[$slot['GameSlot']['id']] = $this->ZuluruTime->time ($slot['GameSlot']['game_start']) . ' ' . $slot['Field']['long_name'];
		}
	}
}
?>
<?php if (!empty($division['Game'])):?>
<?php
	$future_week = 99;
	$dates = array_unique(Set::extract ('/Game/GameSlot/game_date', $division));
	$weeks = array();
	$week = 0;
	$first_day = Configure::read('organization.first_day');
	foreach ($dates as $date) {
		$date_stamp = strtotime($date);
		if ($is_tournament) {
			++ $week;
		} else {
			$week = date('W', $date_stamp);
			if (date('N', $date_stamp) >= $first_day) {
				++ $week;
			}
		}
		if (!array_key_exists($week, $weeks)) {
			$weeks[$week] = array($date, $date);
		} else {
			$weeks[$week][0] = min($date, $weeks[$week][0]);
			$weeks[$week][1] = max($date, $weeks[$week][1]);
		}

		if ($date_stamp > time() && $future_week == 99) {
			$future_week = $week;
		}
	}

	if ($future_week != 99) {
		echo $this->Html->para(null, $this->Html->link(__('Jump to upcoming games', true), "#{$weeks[$future_week][0]}"));
	}
?>
	<table class="list">
	<?php
	$competition = ($division['Division']['schedule_type'] == 'competition');
	?>
	<tr>
		<th><?php if ($is_tournament): ?><?php __('Game'); ?><?php endif; ?></th>
		<?php if ($multi_day): ?>
		<th><?php __('Date'); ?></th>
		<?php endif; ?>
		<th><?php __('Time'); ?></th>
		<th><?php __(Configure::read('sport.field_cap')); ?></th>
		<th><?php __($competition ? 'Team' : 'Home'); ?></th>
		<?php if (!$competition): ?>
		<th><?php __('Away'); ?></th>
		<?php endif; ?>
		<th><?php __('Score'); ?></th>
	</tr>
	<?php
	foreach ($weeks as $week) {
		if ($edit_date >= $week[0] && $edit_date <= $week[1]) {
			echo $this->element('leagues/schedule/week_edit', compact ('week', 'multi_day', 'slots', 'is_manager', 'is_tournament'));
		} else {
			echo $this->element('leagues/schedule/week_view', compact ('week', 'multi_day', 'is_manager'));
		}
	}
	?>
	</table>
<?php endif; ?>

<?php
if (!empty ($edit_date)) {
	echo $this->Form->end();
}
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
