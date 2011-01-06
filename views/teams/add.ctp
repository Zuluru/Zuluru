<div class="teams form">
<?php echo $this->Form->create('Team');?>
	<fieldset>
 		<legend><?php printf(__('Add %s', true), __('Team', true)); ?></legend>
	<?php
		echo $this->Form->input('name');
		echo $this->Form->input('league_id');
		echo $this->Form->input('website');
		echo $this->Form->input('shirt_colour');
		echo $this->Form->input('home_field');
		echo $this->Form->input('region_preference');
		echo $this->Form->input('open_roster');
		echo $this->Form->input('rating');
		echo $this->Form->input('Person');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(sprintf(__('List %s', true), __('Teams', true)), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(sprintf(__('List %s', true), __('Leagues', true)), array('controller' => 'leagues', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('New %s', true), __('League', true)), array('controller' => 'leagues', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('List %s', true), __('Incidents', true)), array('controller' => 'incidents', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('New %s', true), __('Incident', true)), array('controller' => 'incidents', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('List %s', true), __('People', true)), array('controller' => 'people', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('New %s', true), __('Person', true)), array('controller' => 'people', 'action' => 'add')); ?> </li>
	</ul>
</div>