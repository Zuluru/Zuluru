<?php $year = date('Y'); ?>
<h4>Type: Data</h4>
<p>The MEMBER_TYPE rule accepts a YYYY-MM-DD formatted date and returns a string describing the highest membership in effect for the player on that date. The date must be enclosed in quotes.</p>
<p>Currently, the possible member types are "none" (they do not have a membership in effect on the given date), "intro" or "full". The membership type and valid dates are determined from the configuration of the membership events that the player has registered <strong>and paid</strong> for.</p>
<p>Example:</p>
<pre>MEMBER_TYPE('<?php echo $year; ?>-06-01')</pre>
<p>would return one of <strong>none</strong>, <strong>intro</strong> or <strong>full</strong>, depending on the player's registration history.</p>
<?php // TODO: Include suggestions for how to configure membership events and rules for consistency ?>
