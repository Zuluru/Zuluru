<div class="users login">
<h2><?php __('Login'); ?></h2>	
<?php echo $this->element('users/login_notice'); ?>
<?php if ($failed): ?>
<p><strong>NOTE:</strong> If you already have an account from a previous season, <strong>DO NOT CREATE ANOTHER ONE</strong>! Instead, please <a href="<?php echo Configure::read('urls.password_reset'); ?>">follow these instructions</a> to regain access to your account.</p>
<?php endif; ?>
<?php
	echo $this->Form->create('User', array('action' => 'login'));
	echo $this->Form->input("$model.$user_field", array('label' => false, 'id' => 'UserName', 'placeholder' => 'User Name', 'tabindex' => 1,
			'after' => $this->Html->para(null, $this->Html->link('I forgot my username', array('action' => 'reset_password')))));
	echo $this->Form->input("$model.$pwd_field", array('type' => 'password', 'label' => false, 'placeholder' => 'Password', 'tabindex' => 1,
			'after' => $this->Html->para(null, $this->Html->link('I forgot my password', array('action' => 'reset_password')))));
	echo $this->Form->input("$model.remember_me", array('type' => 'checkbox', 'tabindex' => 1));
	echo $this->Form->submit(__('Login', true), array('tabindex' => 1));
	echo $this->Form->end();
	$this->Js->buffer('jQuery("#UserName").focus()');
?>
</div>
