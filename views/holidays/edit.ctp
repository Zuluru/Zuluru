<div class="holidays form">
<?php echo $this->Form->create('Holiday');?>
	<fieldset>
		<legend><?php __('Edit Holiday'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('date');
		echo $this->Form->input('name');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('Delete', true), array('action' => 'delete', $this->Form->value('Holiday.id')), null, sprintf(__('Are you sure you want to delete # %s?', true), $this->Form->value('Holiday.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Holidays', true), array('action' => 'index'));?></li>
	</ul>
</div>

<?php echo $this->ZuluruHtml->script ('datepicker', array('inline' => false));
