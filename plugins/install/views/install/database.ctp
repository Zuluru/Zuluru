<div class="install form">
	<h2><?php echo $title_for_layout; ?></h2>
	<?php
		echo $this->Form->create('Install', array('url' => array('plugin' => 'install', 'controller' => 'install', 'action' => 'database')));
		echo $this->Form->input('driver', array(
			'default' => 'mysql',
			'empty' => false,
			'options' => array(
				'mysql' => 'mysql',
				'mysqli' => 'mysqli',
				'sqlite' => 'sqlite',
				'postgres' => 'postgres',
				'mssql' => 'mssql',
				'db2' => 'db2',
				'oracle' => 'oracle',
				'firebird' => 'firebird',
				'sybase' => 'sybase',
				'odbc' => 'odbc',
			),
		));
		echo $this->Form->input('host', array('default' => 'localhost'));
		echo $this->Form->input('login', array('label' => 'User / Login', 'default' => 'root'));
		echo $this->Form->input('password');
		echo $this->Form->input('database', array('label' => 'Name', 'default' => 'zuluru'));
		echo $this->Form->input('port', array('label' => 'Port (leave blank if unknown)'));
		echo $this->Form->end('Submit');
		echo $this->Html->para(null, 'Ensure that the database user has CREATE, ALTER and DROP permissions on the database.');
	?>
</div>
