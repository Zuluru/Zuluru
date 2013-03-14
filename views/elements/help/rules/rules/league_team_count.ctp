<?php $year = date('Y'); ?>
<h4>Type: Data</h4>
<p>The LEAGUE_TEAM_COUNT rule accepts a comma-separated list of league ids and returns a count of how many teams the player is/was on that play/played in those leagues.</p>
<p>By default, only teams where the player is listed as a captain, assistant captain or regular player, and is accepted on the roster, are counted. You can also include teams where the player is listed as a substitute by including 'include_subs' anywhere in the league id list.</p>
<p>Note that this looks at all divisions within the specified leagues.</p>
<p>Example:</p>
<pre>LEAGUE_TEAM_COUNT(123)</pre>
<p>would return the number of teams playing in league #123 that the player is on.</p>
<pre>LEAGUE_TEAM_COUNT(123,124)</pre>
<p>would return the number of teams playing in leagues #123 or 124 that the player is on.</p>
<pre>LEAGUE_TEAM_COUNT(123,124,include_subs)</pre>
<p>would return the number of teams playing in leagues #123 or 124 that the player is on, even as a substitute.</p>
