<p><?php printf(__('TinyMCE is a popular JavaScript WYSIWYG HTML editor. Using it with %s requires a third-party CakePHP plugin, with a couple of modifications.', true), ZULURU); ?></p>
<p><?php __('One way to install this is something along these lines:'); ?>
<pre><code>$ cd /path/to/zuluru/plugins
$ mkdir tiny_mce
$ cd tiny_mce
$ git init
$ git pull git://github.com/CakeDC/TinyMCE.git 1.3</code></pre>
</p>
<p><?php
printf(__('You will need to make sure that the %s folder is available through your web server, and that %s has the correct URL in the beforeRender function.', true),
	'tiny_mce/webroot/js', 'tiny_mce/views/helpers/tiny_mce.php'
); ?></p>
<p><?php printf(__('Lastly, edit %s and replace', true), 'tiny_mce/views/helpers/tiny_mce.php'); ?>
<pre><code>public $configs = array();</code></pre>
<?php __('with'); ?>
<pre><code>public $configs = array(
	'simple' => array(
		'mode' => 'textareas',
		'theme' => 'advanced',
		'theme_advanced_buttons1' => 'bold,italic,underline,strikethrough,|,undo,redo,|,cleanup,|,formatselect,bullist,numlist',
		'theme_advanced_buttons2' => '',
		'theme_advanced_buttons3' => '',
		'theme_advanced_statusbar_location' => 'bottom',
		'theme_advanced_path' => false,
		'theme_advanced_resizing' => true,
		'editor_selector' => 'mceSimple',
	),
	'advanced' => array(
		'mode' => 'textareas',
		'theme' => 'advanced',
		'theme_advanced_statusbar_location' => 'bottom',
		'theme_advanced_path' => false,
		'theme_advanced_resizing' => true,
		'editor_selector' => 'mceAdvanced',
	),
	'newsletter' => array(
		'mode' => 'textareas',
		'theme' => 'advanced',
		'theme_advanced_statusbar_location' => 'bottom',
		'theme_advanced_path' => false,
		'theme_advanced_resizing' => true,
		'relative_urls' => false,
		'remove_script_host' => false,
		'convert_urls' => false,
		'editor_selector' => 'mceNewsletter',
	),
);</code></pre>
<?php printf(__('to set up %s\'s expected themes, and', true), ZULURU); ?>
<pre><code>$lines .= Inflector::underscore($option) . ' : "' . $value . '",' . "\n";</code></pre>
<?php __('with'); ?>
<pre><code>$lines .= Inflector::underscore($option) . ' : ';
if ($value === true) {
	$lines .= "true,\n";
} else if ($value === false) {
	$lines .= "false,\n";
} else {
	$lines .= '"' . $value . '",' . "\n";
}</code></pre>
<?php __('to ensure that true and false configuration values are set as JavaScript booleans instead of strings.'); ?></p>
