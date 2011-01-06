<?php if ($failed): ?>
<p><strong>NOTE:</strong> If you already have an account from a previous season, <strong>DO NOT CREATE ANOTHER ONE</strong>! Instead, please <a href="<?php echo Configure::read('urls.password_reset'); ?>">follow these instructions</a> to regain access to your account.</p>
<?php
	endif;
    echo $this->Form->create('User', array('action' => 'login'));
    echo $this->Form->input("$model.$user_field", array('label' => 'User name', 'id' => 'UserName'));
    echo $this->Form->input("$model.$pwd_field", array('type' => 'password', 'label' => 'Password'));
	echo $this->Form->input("$model.remember_me", array('type' => 'checkbox'));
    echo $this->Form->end(__('Login', true));
	$this->Js->buffer('$("#UserName").focus()');
?>
