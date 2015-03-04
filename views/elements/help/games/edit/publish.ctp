<p><?php
printf(__('Schedules are typically created unpublished, to allow the coordinator a chance to make any required adjustments (e.g. if a particular team needs to play on a specific %s or at a specific time, or if there is a team matchup that needs to be guaranteed) before people see them and start making plans.', true),
	__(Configure::read('ui.field'), true)
);
?></p>
<p><?php __('If games are published, they will be visible to everyone; otherwise, they will be visible only to admins and coordinators, where they will be highlighted so it\'s obvious that they aren\'t yet published.'); ?></p>
<p><?php __('A day\'s games can be published during the creation or edit process by checking the "publish" box, or with the "publish" link from the league schedule page.'); ?></p>
