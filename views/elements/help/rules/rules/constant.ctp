<h4><?php __('Type: Data'); ?></h4>
<p><?php printf(__('The %s rule simply returns it\'s argument. It is most frequently invoked by simply specifying a quoted string.', true), 'CONSTANT'); ?></p>
<p><?php __('Example:'); ?></p>
<pre>CONSTANT("Male")
"Male'
'Male'</pre>
<p><?php printf(__('All of these will return the string %s', true), '<strong>Male</strong>.'); ?></p>
