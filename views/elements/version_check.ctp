<?php
Configure::load('installed');
if (ZULURU_MAJOR . '.' . ZULURU_MINOR . '.' . ZULURU_REVISION != Configure::read('installed.version') ||
	SCHEMA_VERSION != Configure::read('installed.schema_version')):
?>
<div class="warning">
<?php
echo $this->Html->para(null, sprintf(__('This is Zuluru version %d.%d.%d, database schema version %d.', true), ZULURU_MAJOR, ZULURU_MINOR, ZULURU_REVISION, SCHEMA_VERSION));
echo $this->Html->para(null, sprintf(__('Your installation of version %s, database schema version %d, is dated %s.', true), Configure::read('installed.version'), Configure::read('installed.schema_version'), Configure::read('installed.date')));
echo $this->Html->para(null, __('To ensure proper operation, please', true) . ' ' .
		$this->Html->link(__('complete your update', true), array('plugin' => 'install', 'controller' => 'install', 'action' => 'update')) . '.');
?>
</div>
<?php endif; ?>