<h3><?php __('Single blank, unscheduled game'); ?></h3>
<p><?php printf(__('Creates a single game on a chosen date. No teams or %s are assigned.', true), __(Configure::read('ui.field'), true)); ?></p>
<h3><?php __('Set of blank unscheduled games for all teams in a division'); ?></h3>
<p><?php printf(__('Creates enough blank games to schedule the entire division on a chosen date. No teams or %s are assigned.', true), __(Configure::read('ui.fields'), true)); ?></p>
<h3><?php __('Set of randomly scheduled games for all teams in a division'); ?></h3>
<p><?php printf(__('Creates enough games to schedule the entire division on a chosen date. Teams are assigned randomly and %s assigned according to settings.', true), __(Configure::read('ui.fields'), true)); ?></p>
<h3><?php __('Full-division round-robin'); ?></h3>
<p><?php printf(__('Creates enough games, over a series of weeks, to schedule each team in the division against each other team once. %s are assigned according to settings.', true), __(Configure::read('ui.fields_cap'), true)); ?></p>
<h3><?php __('Half-division round-robin, with 2 pools (top, bottom) divided by team standings.'); ?></h3>
<p><?php printf(__('Creates enough games, over a series of weeks, to schedule each team in the top half of the division against each other team in the top half, and the same for the bottom half. "Top half" is determined based on team win/loss records. %s are assigned according to settings.', true), __(Configure::read('ui.fields_cap'), true)); ?></p>
<h3><?php __('Half-division round-robin, with 2 pools (top/bottom) divided by rating.'); ?></h3>
<p><?php printf(__('Creates enough games, over a series of weeks, to schedule each team in the top half of the division against each other team in the top half, and the same for the bottom half. "Top half" is determined based on team ratings. %s are assigned according to settings.', true), __(Configure::read('ui.fields_cap'), true)); ?></p>
<h3><?php __('Half-division round-robin, with 2 even (interleaved) pools divided by team standings.'); ?></h3>
<p><?php printf(__('Creates enough games, over a series of weeks, to schedule each team in each of two even pools formed from the division against each other team in the same pool. One pool consists of the teams in first, third, fifth, etc. and the other pool consists of the teams in second, fourth, sixth, etc. %s are assigned according to settings.', true), __(Configure::read('ui.fields_cap'), true)); ?></p>
