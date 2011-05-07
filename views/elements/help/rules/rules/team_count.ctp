<?php $year = date('Y'); ?>
<h4>Type: Data</h4>
<p>The TEAM_COUNT rule accepts a YYYY-MM-DD formatted date and returns a count of how many teams the player is/was on that play/played in leagues that are/were open on this date. The date must be enclosed in quotes.</p>
<p>Only teams where the player is listed as a captain, assistant captain or regular player, and is accepted on the roster, are counted.</p>
<p>Example:</p>
<pre>TEAM_COUNT('<?php echo $year; ?>-06-01')</pre>
<p>would return the number of teams playing in the summer of <?php echo $year; ?> that the player is on.</p>
