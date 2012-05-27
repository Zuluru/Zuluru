<p>TinyMCE is a popular JavaScript WYSIWYG HTML editor. Using it with Zuluru requires a third-party CakePHP plugin, with a couple of modifications.</p>
<p>One way to install this is something along these lines:
<pre><code>$ cd /path/to/zuluru/plugins
$ mkdir tiny_mce
$ cd tiny_mce
$ git init
$ git pull git://github.com/CakeDC/TinyMCE.git</code></pre>
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
        'editor_selector' => 'mceAdvanced',
    ),
);</code></pre>
</p>
