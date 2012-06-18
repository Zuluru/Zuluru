<h3>Single blank, unscheduled game</h3>
<p>Creates a single game on a chosen date. No teams or <?php __(Configure::read('ui.field')); ?> are assigned.</p>
<h3>Set of blank unscheduled games for all teams in a division</h3>
<p>Creates enough blank games to schedule the entire division on a chosen date. No teams or <?php __(Configure::read('ui.fields')); ?> are assigned.</p>
<h3>Set of randomly scheduled games for all teams in a division</h3>
<p>Creates enough games to schedule the entire division on a chosen date. Teams are assigned randomly and <?php __(Configure::read('ui.fields')); ?> assigned according to settings.</p>
<h3>Full-division round-robin</h3>
<p>Creates enough games, over a series of weeks, to schedule each team in the division against each other team once. <?php __(Configure::read('ui.fields_cap')); ?> are assigned according to settings.</p>
<h3>Half-division round-robin. 2 pools (top, bottom) divided by team standings.</h3>
<p>Creates enough games, over a series of weeks, to schedule each team in the top half of the division against each other team in the top half, and the same for the bottom half. "Top half" is determined based on team win/loss records. <?php __(Configure::read('ui.fields_cap')); ?> are assigned according to settings.</p>
<h3>Half-division round-robin. 2 pools (top/bottom) divided by rating.</h3>
<p>Creates enough games, over a series of weeks, to schedule each team in the top half of the division against each other team in the top half, and the same for the bottom half. "Top half" is determined based on team ratings. <?php __(Configure::read('ui.fields_cap')); ?> are assigned according to settings.</p>
<h3>Half-division round-robin. 2 even (interleaved) pools divided by team standings.</h3>
<p>Creates enough games, over a series of weeks, to schedule each team in each of two even pools formed from the division against each other team in the same pool. One pool consists of the teams in first, third, fifth, etc. and the other pool consists of the teams in second, fourth, sixth, etc. <?php __(Configure::read('ui.fields_cap')); ?> are assigned according to settings.</p>
