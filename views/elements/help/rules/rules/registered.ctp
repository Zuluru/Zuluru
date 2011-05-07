<h4>Type: Boolean</h4>
<p>The REGISTERED rule accepts a comma-separated list of integers and returns true if the user has registered for at least one of them. Payment status is NOT checked, in order to allow people to register for multiple items and pay all at once.</p>
<p>Example:</p>
<pre>REGISTERED(123)</pre>
<p>will return <em>true</em> if the person has registered for event #123, <em>false</em> otherwise.</p>
<pre>REGISTERED(1,12,123)</pre>
<p>will return <em>true</em> if the person has registered for at least one of events #1, 12 or 123, <em>false</em> otherwise.</p>
