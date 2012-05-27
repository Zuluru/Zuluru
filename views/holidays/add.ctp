<div class="holidays form">
<?php echo $this->Form->create('Holiday');?>
	<fieldset>
		<legend><?php __('Add Holiday'); ?></legend>
	<?php
		echo $this->Form->input('date');
		echo $this->Form->input('name');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Holidays', true), array('action' => 'index'));?></li>
	</ul>
</div>