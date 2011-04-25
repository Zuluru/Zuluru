<?php
$this->Html->addCrumb (__('Leagues', true));
$this->Html->addCrumb ($league['League']['long_name']);
$this->Html->addCrumb (__('Schedule', true));
?>

<div class="leagues schedule">
<h2><?php echo __('League Schedule', true) . ': ' . $league['League']['long_name'];?></h2>
<?php
if (!empty ($edit_date)) {
	echo $this->Form->create ('Game', array('url' => $this->here));

	// Put the slots into a more useful form for us
	$slots = $game_slot = array();
	foreach ($game_slots as $slot) {
		$slots[$slot['GameSlot']['id']] = $this->ZuluruTime->time ($slot['GameSlot']['game_start']) . ' ' . $slot['Field']['long_name'];
		$game_slot[$slot['Game']['id']] = $slot['GameSlot']['id'];
	}
	asort ($slots);
}
?>
<?php if (!empty($league['Game'])):?>
<?php
	$future = array_shift (Set::extract('/Game/GameSlot[game_date>=' . date('Y-m-d') . ']/game_date', $league));
	if ($future) {
		echo $this->Html->para(null, $this->Html->link(__('Jump to upcoming games', true), "#$future"));
	}
?>
	<table>
	<?php
	$dates = array_unique(Set::extract ('/Game/GameSlot/game_date', $league));
	foreach ($dates as $date) {
		if ($date == $edit_date) {
			echo $this->element ('league/schedule/week_edit', compact ('date', 'slots', 'game_slot'));
		} else {
			echo $this->element ('league/schedule/week_view', compact ('date'));
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
			array('action' => 'view', 'league' => $league['League']['id']),
			array('alt' => __('Details', true), 'title' => __('View League Details', true))));
		echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('standings_32.png',
			array('action' => 'standings', 'league' => $league['League']['id']),
			array('alt' => __('Standings', true), 'title' => __('Standings', true))));
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
			// TODO: More links to reports, etc.
		}
		if ($is_admin) {
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('coordinator_add_32.png',
				array('action' => 'add_coordinator', 'league' => $league['League']['id']),
				array('alt' => __('Add Coordinator', true), 'title' => __('Add Coordinator', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('spirit_32.png',
				array('action' => 'spirit', 'league' => $league['League']['id']),
				array('alt' => __('Spirit', true), 'title' => __('See League Spirit Report', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('delete_32.png',
				array('action' => 'delete', 'league' => $league['League']['id']),
				array('alt' => __('Delete', true), 'title' => __('Delete League', true)),
				array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $league['League']['id']))));
		}
		?>
	</ul>
</div>
