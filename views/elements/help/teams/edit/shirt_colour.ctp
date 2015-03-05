<p><?php
printf(__('Shirt colour can be whatever you want, but if you pick a common colour you\'ll get a properly-coloured shirt icon next to your team name in various displays. Examples include %s, %s and %s. If you have two options, list them both. For example, "%s" will show like this: %s. If you get the "unknown" shirt %s, this means that your colour is not supported.', true),
	'yellow ' . $this->ZuluruHtml->icon('shirts/yellow.png'),
	'light blue ' . $this->ZuluruHtml->icon('shirts/light_blue.png'),
	'dark ' . $this->ZuluruHtml->icon('shirts/dark.png'),
	'blue or white', $this->element('shirt', array('colour' => 'blue or white')),
	$this->ZuluruHtml->icon('shirts/default.png')
);
if ($is_admin) {
	printf(' ' . __('Additional shirt colours can be added simply by placing appropriately-named icons in the %s folder.', true), '&lt;webroot&gt;/img/shirts');
}
?></p>
