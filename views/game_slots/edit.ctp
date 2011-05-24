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
		echo $this->ZuluruForm->input('game_date');
		echo $this->ZuluruForm->input('game_start');
		echo $this->ZuluruForm->input('game_end');
		echo $this->ZuluruForm->input('league_id', array(
				'multiple' => 'checkbox',
		));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
