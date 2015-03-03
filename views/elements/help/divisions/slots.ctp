<p><?php
printf(__('The %s Availability Report is used by coordinators over the course of the season to assist in ensuring that premier %s and time slots are being fully utilized.', true),
	__(Configure::read('ui.field_cap'), true), __(Configure::read('ui.fields'), true)
);
?></p>
<p><?php
printf(__('After selecting a date for which you want to see the report, it will show a list of all game slots available to this division, and whether or not they are assigned. If assigned, it will show home and away teams%s.', true),
	(Configure::read('feature.region_preference') ? __(', and the regional preference of the home team', true) : '')
);
?>.</p>
<p><?php __('You can also access this report for a particular date through direct links on the division schedule.'); ?></p>
