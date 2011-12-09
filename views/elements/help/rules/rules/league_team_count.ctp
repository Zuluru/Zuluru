<?php $year = date('Y'); ?>
<h4>Type: Data</h4>
<p>The LEAGUE_TEAM_COUNT rule accepts a comma-separated list of league ids and returns a count of how many teams the player is/was on that play/played in those leagues.</p>
<p>Only teams where the player is listed as a captain, assistant captain or regular player, and is accepted on the roster, are counted.</p>
<p>Example:</p>
<pre>LEAGUE_TEAM_COUNT(123)</pre>
<p>would return the number of teams playing in league #123 that the player is on.</p>
<pre>LEAGUE_TEAM_COUNT(123,124)</pre>
<p>would return the number of teams playing in leagues #123 or 124 that the player is on.</p>
