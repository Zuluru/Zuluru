<?php
$this->Html->addCrumb (__('Users', true));
$this->Html->addCrumb ($user['User']['full_name']);
$this->Html->addCrumb (__('Change Password', true));
?>

<div class="users form">
<?php echo $this->Form->create('User', array('url' => Router::normalize($this->here)));?>
	<fieldset>
 		<legend><?php echo __('Change Password for', true) . ' ' . $user['User']['full_name']; ?></legend>
	<?php
		echo $this->Form->input('id');
		if (!$is_admin || $is_me)
			echo $this->Form->input('passold', array('type' => 'password', 'label' => 'Existing Password', 'value' => ''));
		echo $this->Form->input('passwd', array('type' => 'password', 'label' => 'New Password'));
		echo $this->Form->input('confirm_passwd', array('type' => 'password', 'label' => 'Confirm Password'));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
