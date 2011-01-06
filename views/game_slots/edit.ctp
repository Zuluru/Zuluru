<?php
$this->Html->addCrumb (__('Game Slot', true));
$this->Html->addCrumb (__('Edit', true));
?>

<div class="gameSlots form">
<?php echo $this->Form->create('GameSlot', array('url' => $this->here));?>
	<fieldset>
 		<legend><?php printf(__('Edit %s', true), __('Game Slot', true)); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('game_date');
		echo $this->Form->input('game_start');
		echo $this->Form->input('game_end');
		echo $this->Form->input('league_id', array(
				'multiple' => 'checkbox',
		));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
