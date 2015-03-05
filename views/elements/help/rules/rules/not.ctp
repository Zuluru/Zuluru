<h4><?php __('Type: Boolean'); ?></h4>
<p><?php __('The NOT rule accepts one rule, returning <em>true</em> if that rule is false, <em>true</em> otherwise.'); ?></p>
<p><?php printf(__('Note that this is infrequently used, as most rules are built using %s, which supports negation via the != operator.', true), 'COMPARE'); ?></p>
<p><?php __('Example:'); ?></p>
<pre>NOT(REGISTERED(123))</pre>
<p><?php __('will return <em>false</em> if the person has registered for event #123, <em>true</em> otherwise.'); ?></p>
