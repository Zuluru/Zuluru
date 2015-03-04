<p><?php printf(__('iCal is a standardized format for exchanging schedule information between applications. %s supports iCal output in a variety of ways, but perhaps the most useful is the "Personal Feed".', true), ZULURU); ?></p>
<p><?php
printf(__('If you %s to enable this, you will be able to have iCal, Google Calendar and others automatically pull your schedule, from week to week, season to season, and year to year, and keep you informed of all of your upcoming games.', true),
	$this->Html->link (__('edit your preferences', true), array('controller' => 'people', 'action' => 'preferences'))
); ?></p>
<p><?php printf(__('To add your personal feed to iCal, copy the link from the iCal logo at the bottom of the main %s page. Then, go to the Calendar menu in iCal, pick Subscribe, and paste in the link.', true), ZULURU); ?></p>
<p><?php printf(__('To add your personal feed to Google Calendar, just click the "Add to Google Calendar" link at the bottom of the main page.', true), ZULURU); ?></p>
