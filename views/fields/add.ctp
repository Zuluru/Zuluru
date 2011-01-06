<div class="fields form">
<?php echo $this->Form->create('Field');?>
	<fieldset>
 		<legend><?php printf(__('Add %s', true), __('Field', true)); ?></legend>
	<?php
		echo $this->Form->input('num');
		echo $this->Form->input('is_open');
		echo $this->Form->input('rating');
		echo $this->Form->input('notes');
		echo $this->Form->input('parent_id');
		echo $this->Form->input('name');
		echo $this->Form->input('code');
		echo $this->Form->input('location_street');
		echo $this->Form->input('location_city');
		echo $this->Form->input('location_province');
		echo $this->Form->input('latitude');
		echo $this->Form->input('longitude');
		echo $this->Form->input('angle');
		echo $this->Form->input('length');
		echo $this->Form->input('width');
		echo $this->Form->input('zoom');
		echo $this->Form->input('parking');
		echo $this->Form->input('region_id');
		echo $this->Form->input('driving_directions');
		echo $this->Form->input('parking_details');
		echo $this->Form->input('transit_directions');
		echo $this->Form->input('biking_directions');
		echo $this->Form->input('washrooms');
		echo $this->Form->input('public_instructions');
		echo $this->Form->input('site_instructions');
		echo $this->Form->input('sponsor');
		echo $this->Form->input('location_url');
		echo $this->Form->input('layout_url');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(sprintf(__('List %s', true), __('Fields', true)), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(sprintf(__('List %s', true), __('Fields', true)), array('controller' => 'fields', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('New %s', true), __('Parent Field', true)), array('controller' => 'fields', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('List %s', true), __('Regions', true)), array('controller' => 'regions', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('New %s', true), __('Region', true)), array('controller' => 'regions', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('List %s', true), __('Game Slots', true)), array('controller' => 'game_slots', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('New %s', true), __('Game Slot', true)), array('controller' => 'game_slots', 'action' => 'add')); ?> </li>
	</ul>
</div>