<p class="error-message"><?php printf (__('You cannot register for any events until you are %s to the site. The system can help you %s or %s.', true),
	$this->Html->link (__('logged on', true), Configure::read('urls.login')),
	$this->Html->link (__('recover forgotten passwords', true), Configure::read('urls.password_reset')),
	$this->Html->link (sprintf (__('create a new profile (and user ID with password) if you are new to the %s site', true), Configure::read('organization.short_name')), Configure::read('urls.register'))); ?></p>
