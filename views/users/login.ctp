<h2><?php __('Login'); ?></h2>
<?php echo $this->element('users/login_notice'); ?>

<?php if ($failed): ?>
<div class="flash flash-warn">
	<?php
		printf(
			__('%s If you already have an account from a previous season, %s!', true),
			$this->Html->tag('strong', __('NOTE', true) . ': '),
			$this->Html->tag('strong', __('DO NOT CREATE ANOTHER ONE', true))
		);
	?>
	<p>
		<?php
			printf(
			__('Instead, please %s to regain access to your account.', true),
				$this->Html->link(
					__('follow these instructions', true),
					array('controller' => 'users', 'action' => 'reset_password')
				)
			);
		?>
	</p>
</div>
<?php endif; ?>

<?php

echo $this->Form->create('User', array(
	'action' => 'login',
));

?>
	<dl class="form">
		<dt><label>Username</label></dt>
		<dd>
			<?php
				echo $this->Form->input("$model.$user_field", array(
					'label' => false,
					'placeholder' => __('Username', true),
					'tabindex' => 1,
					'autofocus' => 'autofocus',
					'after' => $this->Html->para(
						null,
						$this->Html->link(__('I forgot my username', true),
						array('action' => 'reset_password'))
					),
				));
			?>
		</dd>
	</dl>

	<dl class="form">
		<dt><label>Password</label></dt>
		<dd>
			<?php
				echo $this->Form->input("$model.$pwd_field", array(
					'type' => 'password',
					'label' => false,
					'placeholder' => __('Password', true),
					'tabindex' => 2,
					'after' => $this->Html->para(
						null,
						$this->Html->link(
							__('I forgot my password', true),
							array('action' => 'reset_password')
					 )),
				));
			?>
		</dd>
	</dl>

	<dl class="form">
		<dd>
			<?php
				echo $this->Form->input("$model.remember_me", array(
					'type' => 'checkbox',
					'tabindex' => 3,
				));
			?>
		</dd>
	</dl>

<?php

echo $this->Form->submit(__('Login', true), array(
	'class' => 'btn btn-default',
	'tabindex' => 4,
));

echo $this->Form->end();

$this->Js->buffer('jQuery("#UserName").focus()');
?>
