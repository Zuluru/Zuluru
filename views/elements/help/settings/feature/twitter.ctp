<p><?php printf(__('To enable Twitter integration, you must first install two support libraries into %s.', true), 'zuluru/libs'); ?></p>
<p><?php
printf(__('From %s you need %s (renamed to %s) and %s.', true),
	$this->Html->link('tmhOAuth', 'https://github.com/themattharris/tmhOAuth'),
	'tmhOAuth.php', 'tmh_oauth.php', 'cacert.pem'
); ?></p>
<p><?php
printf(__('From %s you need %s (renamed to %s).', true),
	$this->Html->link('twitter-api-php', 'https://github.com/J7mbo/twitter-api-php'),
	'TwitterAPIExchange.php', 'twitter_api_exchange.php'
); ?></p>
<p><?php
printf(__('After this, you must set the Consumer Key and Consumer Secret values. You can obtain standard %s values for these by contacting %s, or you can acquire your own. If you want your own consumer values, log in to Twitter, then go to the %s, click "Create a new application", and follow the steps.', true),
	ZULURU,
	$this->Html->link('admin@zuluru.org', 'mailto:admin@zuluru.org'),
	$this->Html->link(__('Twitter "My Applications" page', true), 'https://dev.twitter.com/apps')
); ?></p>
