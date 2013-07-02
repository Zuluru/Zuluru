<?php
$this->Html->addCrumb (__('Badges', true));
$this->Html->addCrumb (__('Nominate', true));
?>

<div class="badges form">
<?php echo $this->Form->create(false, array('url' => Router::normalize($this->here)));?>
	<fieldset>
 		<legend><?php __('Nominate for a Badge'); ?></legend>
	<?php
		echo $this->Form->input('badge', array(
				'options' => $badges,
				'empty' => 'Select one:',
		));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Continue', true));?>
</div>
