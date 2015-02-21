<div class="users login">
<h2><?php __('Login'); ?></h2>	
<?php echo $this->element('users/login_notice'); ?>
<?php if ($failed): ?>
<p><?php printf(__('%s If you already have an account from a previous season, %s! Instead, please %s to regain access to your account.', true),
		$this->Html->tag('strong', __('NOTE', true) . ': '),
		$this->Html->tag('strong', __('DO NOT CREATE ANOTHER ONE', true)),
		$this->Html->link(__('follow these instructions', true), array('controller' => 'users', 'action' => 'reset_password'))
);
?></p>
<?php
endif;

echo $this->Form->create('User', array('action' => 'login'));
echo $this->Form->input("$model.$user_field", array(
		'label' => false,
		'id' => 'UserName',
		'placeholder' => __('User Name', true),
		'tabindex' => 1,
		'after' => $this->Html->para(null, $this->Html->link(__('I forgot my username', true), array('action' => 'reset_password'))),
));
echo $this->Form->input("$model.$pwd_field", array(
		'type' => 'password',
		'label' => false,
		'placeholder' => __('Password', true),
		'tabindex' => 1,
		'after' => $this->Html->para(null, $this->Html->link(__('I forgot my password', true), array('action' => 'reset_password'))),
));
echo $this->Form->input("$model.remember_me", array(
		'type' => 'checkbox',
		'tabindex' => 1,
));
echo $this->Form->submit(__('Login', true), array('tabindex' => 1));
echo $this->Form->end();
$this->Js->buffer('jQuery("#UserName").focus()');
?>
</div>
