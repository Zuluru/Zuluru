<h4>Type: Boolean</h4>
<p>The NOT rule accepts one rule, returning <em>true</em> if that rule is false, <em>true</em> otherwise.</p>
<p>Note that this is infrequently used, as most rules are built using COMPARE, which supports negation via the != operator.</p>
<p>Example:</p>
<pre>NOT(REGISTERED(123))</pre>
<p>will return <em>false</em> if the person has registered for event #123, <em>true</em> otherwise.</p>
