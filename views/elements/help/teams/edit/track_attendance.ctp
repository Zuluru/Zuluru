<p><?php echo ZULURU; ?> includes the capability to manage and track your team's attendance over the season. Attendance management involves sending regular emails to captains and players alike, so it is optional. To turn this on, the captain must enable it in the <?php
if (array_key_exists ('team', $this->passedArgs)) {
	echo $this->Html->link('edit team', array('controller' => 'teams', 'action' => 'edit', 'team' => $this->passedArgs['team']));
} else {
	echo '"edit team"';
}
?> page.</p>
<p>When attendance tracking is enabled, there are additional options that allow you to customize which emails the system will send (reminders to players, game summaries and change notifications to captains), and when they will be sent.</p>
