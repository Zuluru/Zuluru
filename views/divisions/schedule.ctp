<?php
$this->Html->addCrumb (__('Divisions', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('Schedule', true));
?>

<div class="divisions schedule">
<h2><?php echo __('Division Schedule', true) . ': ' . $division['Division']['full_league_name'];?></h2>
<?php
if (!empty ($edit_date)) {
	echo $this->Form->create ('Game', array('url' => Router::normalize($this->here)));

	// Put the slots into a more useful form for us
	$slots = array();
	foreach ($game_slots as $slot) {
		$slots[$slot['GameSlot']['id']] = $this->ZuluruTime->time ($slot['GameSlot']['game_start']) . ' ' . $slot['Field']['long_name'];
	}
	asort ($slots);
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
			echo $this->element('leagues/schedule/week_edit', compact ('date', 'slots'));
		} else {
			echo $this->element('leagues/schedule/week_view', compact ('date'));
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

<div class="actions">
	<ul>
		<?php
		echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('view_32.png',
			array('action' => 'view', 'division' => $division['Division']['id']),
			array('alt' => __('Details', true), 'title' => __('View Division Details', true))));
		echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('standings_32.png',
			array('action' => 'standings', 'division' => $division['Division']['id']),
			array('alt' => __('Standings', true), 'title' => __('Standings', true))));
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
				array('alt' => sprintf(__('%s Distribution', true), Configure::read('sport.field_cap')), 'title' => sprintf(__('%s Distribution Report', true), Configure::read('sport.field_cap')))));
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
				array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $division['League']['id']))));
		}
		?>
	</ul>
</div>
