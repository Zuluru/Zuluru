<div class="install">
	<h2><?php echo $title_for_layout; ?></h2>

	<?php if ($this->action == 'install'): ?>
	<p>
		Username: admin<br />
		Password: password
	</p>
	<?php endif; ?>

	<?php
		if (isset($config_file)) {
			echo $this->Html->para('error', sprintf (__('Failed to write to %s', true), $config_file));
			echo $this->Html->para('error', __('To ensure that future updates go smoothly, please update it manually with the following:', true));
			echo $this->Html->tag('pre', htmlentities($config_contents));
		}

		echo $this->Html->para(null, __('Delete the installation directory', true) . ' ' .
			$this->Html->tag('strong', '/app/plugins/install') . '.');
		echo $this->Html->link(__('Click here to delete installation files', true), array(
			'plugin' => 'install',
			'controller' => 'install',
			'action' => 'finish',
			'delete' => 1,
		));
	?>
</div>
