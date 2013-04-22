<p>TinyMCE is a popular JavaScript WYSIWYG HTML editor. Using it with <?php echo ZULURU; ?> requires a third-party CakePHP plugin, with a couple of modifications.</p>
<p>One way to install this is something along these lines:
<pre><code>$ cd /path/to/zuluru/plugins
$ mkdir tiny_mce
$ cd tiny_mce
$ git init
$ git pull git://github.com/CakeDC/TinyMCE.git 1.3</code></pre>
</p>
<p>You will need to make sure that the tiny_mce/webroot/js folder is available through your web server, and that tiny_mce/views/helpers/tiny_mce.php has the correct URL in the beforeRender function.</p>
<p>Lastly, edit tiny_mce/views/helpers/tiny_mce.php and replace
<pre><code>public $configs = array();</code></pre>
with
<pre><code>public $configs = array(
    'simple' => array(
        'mode' => 'textareas',
        'theme' => 'simple',
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
to set up Zuluru's expected themes, and
<pre><code>$lines .= Inflector::underscore($option) . ' : "' . $value . '",' . "\n";</code></pre>
with
<pre><code>$lines .= Inflector::underscore($option) . ' : ';
if ($value === true) {
	$lines .= "true,\n";
} else if ($value === false) {
	$lines .= "false,\n";
} else {
	$lines .= '"' . $value . '",' . "\n";
}</code></pre>
to ensure that true and false configuration values are set as JavaScript booleans instead of strings.</p>
