<div class="install form">
	<h2><?php echo $title_for_layout; ?></h2>
	<?php
		echo $this->Form->create('Install', array('url' => array('plugin' => 'install', 'controller' => 'install', 'action' => 'settings')));
		echo $this->Form->input('domain', array('default' => $_SERVER['SERVER_NAME']));
		echo $this->Form->end('Submit');
	?>
</div>
