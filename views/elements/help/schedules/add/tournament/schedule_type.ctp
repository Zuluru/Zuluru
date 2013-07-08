<p>Once the first set of pools is set up, you will want to generate a schedule for each bracket. Once each pool has games scheduled, it will ask you to set up more pools, then schedule games for those pools, and so on until your schedule is complete.</p>
<h3>Single blank, unscheduled game</h3>
<p>Creates a single game on a chosen date. No teams or <?php __(Configure::read('ui.field')); ?> are assigned.</p>
<h3>Set of blank unscheduled games for all teams in the division</h3>
<p>Creates enough games to schedule the entire division on a chosen date. No teams or <?php __(Configure::read('ui.fields')); ?> are assigned.</p>
<h3>Round-robin</h3>
<p>Creates games scheduling each team in the division against each other team once.</p>
<h3>Round-robin with results from prior-stage matchups carried forward</h3>
<p>Creates games scheduling each team in the division against each other team once, except that any games which would match up two teams that have already played are replaced by "dummy" records which are automatically filled in when dependencies for the pool are initialized. This option is not available in the first stage of a tournament.</p>
<h3>Playoff brackets</h3>
<p>You will generally also be given a variety of playoff bracket options, depending on the size of the pool. Brackets will automatically generate all required games to lead to an eventual champion, typically with options regarding whether or not to also create placement games (3rd, 5th, etc.).</p>