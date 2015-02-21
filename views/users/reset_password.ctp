<?php
$this->Html->addCrumb (__('Users', true));
$this->Html->addCrumb (__('Reset Password', true));
?>

<p><?php __('If you have forgotten your password, fill in your user name OR email address below. A personalized reset link will be emailed to the address we have on file.'); ?></p>
<p><?php __('If you no longer have access to the email address on file, you will need to contact the office directly to have this updated before proceeding.'); ?></p>
<?php // TODO: Security question method ?>

<div class="users form">
<?php echo $this->Form->create($user_model, array('url' => Router::normalize($this->here)));?>
	<fieldset>
	<?php
		echo $this->Form->input($user_field, array('label' => __('User Name', true)));
		echo $this->Form->input($email_field, array('label' => __('Email', true)));
	?>
	<p class="highlight-message"><?php __('Only one of these is typically required.'); ?></p>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
