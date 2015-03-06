<?php if ($is_official || $is_volunteer || $is_player || $is_coach): ?>
<p><?php
if (Configure::read('personal.enable_ical')) {
	printf(__('Get your personal schedule in %s format or %s.', true),
		// TODOIMG: Better image locations, alt text
		$this->ZuluruHtml->iconLink ('ical.gif',
			array('controller' => 'people', 'action' => 'ical', $id, 'player.ics'),
			array('alt' => 'iCal')),
		$this->ZuluruHtml->imageLink ('http://www.google.com/calendar/images/ext/gc_button6.gif',
			'http://www.google.com/calendar/render?cid=' . $this->Html->url(array('controller' => 'people', 'action' => 'ical', $id), true),
			array('alt' => 'add to Google Calendar'),
			array('target' => 'google'))
	);
} else {
	printf(__('%s to enable your personal iCal feed', true),
		$this->Html->link (__('Edit your preferences', true), array('controller' => 'people', 'action' => 'preferences'))
	);
}
?> <?php echo $this->ZuluruHtml->help(array('action' => 'games', 'personal_feed')); ?></p>
<?php endif; ?>
