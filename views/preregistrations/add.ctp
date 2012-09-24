<?php
$this->Html->addCrumb (__('Preregistrations', true));
$this->Html->addCrumb (__('Add', true));
?>

<div class="preregistrations form">
<?php echo $this->Form->create(false, array('url' => Router::normalize($this->here)));?>
	<fieldset>
 		<legend><?php __('Add Preregistration'); ?></legend>
	<?php
		echo $this->Form->input('event', array(
				'options' => $events,
				'empty' => 'Select one:',
		));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Continue', true));?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__('List Preregistrations', true), array('action' => 'index'));?></li>
	</ul>
</div>