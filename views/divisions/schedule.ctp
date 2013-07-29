<?php
$this->Html->addCrumb (__('Divisions', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('Schedule', true));
?>

<?php
// Perhaps remove manager status, if we're looking at a different affiliate
if ($is_manager && !in_array($division['League']['affiliate_id'], $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
	$is_manager = false;
}
?>

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
	usort($game_slots, array('GameSlot', 'compareTimeAndField'));
	foreach ($game_slots as $slot) {
		if ($is_tournament) {
			$slots[$slot['GameSlot']['id']] = $this->ZuluruTime->day ($slot['GameSlot']['game_date']) . ' ' . $this->ZuluruTime->time ($slot['GameSlot']['game_start']) . ' ' . $slot['Field']['long_name'];
		} else {
			$slots[$slot['GameSlot']['id']] = $this->ZuluruTime->time ($slot['GameSlot']['game_start']) . ' ' . $slot['Field']['long_name'];
		}
	}
}
?>
<?php if (!empty($division['Game'])):?>
<?php
	$future = array_shift (Set::extract('/Game/GameSlot[game_date>=' . date('Y-m-d') . ']/game_date', $division));
	if ($future) {
		echo $this->Html->para(null, $this->Html->link(__('Jump to upcoming games', true), "#$future"));
	}
?>
	<table class="list">
	<?php
	$dates = array_unique(Set::extract ('/Game/GameSlot/game_date', $division));
	foreach ($dates as $date) {
		if ($date == $edit_date) {
			echo $this->element('leagues/schedule/week_edit', compact ('date', 'slots', 'is_manager', 'is_tournament'));
		} else {
			echo $this->element('leagues/schedule/week_view', compact ('date', 'is_manager'));
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
