<?php
$this->Html->addCrumb (__('Leagues', true));
$this->Html->addCrumb ($league['League']['full_name']);
$this->Html->addCrumb (__('Schedule', true));
?>

<?php
// Perhaps remove manager status, if we're looking at a different affiliate
if ($is_manager && !in_array($league['League']['affiliate_id'], $this->UserCache->read('ManagedAffiliateIDs'))) {
	$is_manager = false;
}
?>

<?php if (!empty($league['League']['header'])): ?>
<div class="league_header"><?php echo $league['League']['header']; ?></div>
<?php endif; ?>
<div class="leagues schedule">
<h2><?php echo __('League Schedule', true) . ': ' . $league['League']['full_name'];?></h2>
<?php
if (in_array('tournament', Set::extract('/Division/schedule_type', $league))) {
	echo $this->element('leagues/schedule/tournament/notice');
}

if (!empty ($edit_date)) {
	echo $this->Form->create ('Game', array('url' => Router::normalize($this->here)));

	// Put the slots into a more useful form for us
	$all_slots = array();
	foreach ($game_slots as $slots) {
		$all_slots = array_merge($all_slots, $slots);
	}
	usort($all_slots, array('GameSlot', 'compareTimeAndField'));
	$slots = array();
	foreach ($all_slots as $slot) {
		if ($is_tournament || $multi_day) {
			$slots[$slot['GameSlot']['id']] = $this->ZuluruTime->day ($slot['GameSlot']['game_date']) . ' ' . $this->ZuluruTime->time ($slot['GameSlot']['game_start']) . ' ' . $slot['Field']['long_name'];
		} else {
			$slots[$slot['GameSlot']['id']] = $this->ZuluruTime->time ($slot['GameSlot']['game_start']) . ' ' . $slot['Field']['long_name'];
		}
	}
}
?>
<?php if (!empty($league['Game'])):?>
<?php
	$future_week = 99;
	if ($is_admin || $is_manager || $is_coordinator) {
		$condition = '';
	} else {
		$condition = '[published=1]';
	}
	$dates = array_unique(Set::extract ("/Game$condition/GameSlot/game_date", $league));
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
	$schedule_types = array_unique(Set::extract('/Division/schedule_type', $league));
	$competition = (count($schedule_types) == 1 && $schedule_types[0] == 'competition');
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

<div class="actions"><?php echo $this->element('leagues/actions', array(
	'league' => $league,
	'format' => 'list',
)); ?></div>
<?php if (!empty($league['League']['footer'])): ?>
<div class="league_footer"><?php echo $league['League']['footer']; ?></div>
<?php endif; ?>
