<p><?php
printf(__('The %s Distribution Report is used by coordinators over the course of the season to ensure that %s and time slots are being assigned to teams in a balanced way. There are typically some %s or time slots that are preferred over others, and this report helps to ensure that everyone gets their fair share of these preferred options.', true),
	__(Configure::read('ui.field_cap'), true), __(Configure::read('ui.fields'), true), __(Configure::read('ui.fields'), true)
);
?></p>
<p><?php
printf(__('The report summarizes all %s at a single facility at the same time. If the league in question has games in more than one region, sub-totals are also provided for each region.', true),
	__(Configure::read('ui.fields'), true)
);
?></p>
<p><?php __('By default, the report includes all games, published or not, but sometimes during the scheduling process it is useful to be able to eliminate the games currently being scheduled from the totals, so there is a link at the top of the report to include only published games.'); ?></p>
