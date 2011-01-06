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
		echo $this->Html->tag ('li', $this->Html->link(__('Details', true), array('action' => 'view', 'league' => $league['League']['id'])));
		echo $this->Html->tag ('li', $this->Html->link(__('Standings', true), array('action' => 'standings', 'league' => $league['League']['id'])));
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
