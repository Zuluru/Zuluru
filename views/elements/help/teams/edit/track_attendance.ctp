<p><?php
printf(__('%s includes the capability to manage and track your team\'s attendance over the season. Attendance management involves sending regular emails to coaches, captains and players, so it is optional. To turn this on, the coach or captain must enable it in the %s page.', true),
	ZULURU,
	(array_key_exists ('team', $this->passedArgs) ? $this->Html->link(__('edit team', true), array('controller' => 'teams', 'action' => 'edit', 'team' => $this->passedArgs['team'])) : '"' . __('edit team', true) . '"')
); ?></p>
<p><?php __('When attendance tracking is enabled, there are additional options that allow you to customize which emails the system will send (reminders to players, game summaries and change notifications to coaches and captains), and when they will be sent.'); ?></p>
