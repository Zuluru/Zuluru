<p class="error-message"><?php printf (__('You cannot register for any events until you are %s to the site and your %s profile has been completed.', true),
	$this->Html->link (__('logged on', true), Configure::read('urls.login')),
	$this->Html->link (Configure::read('site.name'), Configure::read('urls.zuluru_base') . '/')); ?></p>
<p><?php printf (__('The system can help you %s or %s.', true),
	$this->Html->link (__('recover forgotten passwords', true), Configure::read('urls.password_reset')),
	$this->Html->link (sprintf (__('register if you are a new %s member', true), Configure::read('organization.short_name')), Configure::read('urls.register'))); ?></p>
