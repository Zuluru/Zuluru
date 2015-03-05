<p><?php __('Every registration event may have a rule defined that determines who is allowed to register. These rules can look at your gender, age, registration history, and other factors.'); ?></p>
<p><?php
printf(__('The %s attempts to simplify the registration process by only showing you options that you are qualified to register for.', true),
	$this->Html->link(__('registration wizard', true), array('controller' => 'events', 'action' => 'wizard'))
); ?></p>
