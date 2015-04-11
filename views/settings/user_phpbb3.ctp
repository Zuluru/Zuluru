<?php
$this->Html->addCrumb (__('Settings', true));
$this->Html->addCrumb (__('phpBB3', true));
?>

<div class="settings form">
<?php
echo $this->ZuluruForm->create('Settings', array('url' => Router::normalize($this->here)));

echo $this->element('settings/banner');

echo $this->element('settings/input', array(
	'category' => 'phpbb3',
	'name' => 'root_path',
	'options' => array(
		'after' => __('Path to your phpBB3 installation, where config.php is located. Include the trailing slash.', true),
	),
));

echo $this->Form->end(__('Submit', true));
?>
</div>
