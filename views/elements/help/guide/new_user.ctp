<h2><?php __('New User Guide'); ?></h2>
<p><?php
printf(__('For a new user, %s can be a little overwhelming. This guide will help you through the most important things to get you started. After that, you may be interested in the %s, and if you plan on running a team, the %s is a useful resource.', true),
	ZULURU,
	$this->Html->link(__('advanced users guide', true), array('controller' => 'help', 'action' => 'guide', 'advanced')),
	$this->Html->link(__('captains guide', true), array('controller' => 'help', 'action' => 'guide', 'captain'))
); ?></p>

<h2><?php __('User Account and Profile'); ?></h2>
<p><?php
printf(__('Some features of %s (e.g. schedules and standings) are available for anyone to use. However, to participate in the %s, you must have a user account on the system.', true),
	ZULURU, Configure::read('organization.name')
);
echo ' ';
if ($is_logged_in) {
	printf(__('You are already logged in to the system, so it seems that you\'ve successfully taken care of this step. For the record, your user name is \'%s\' and your ID number is %s.', true),
		$this->UserCache->read('Person.user_name'), $this->UserCache->read('Person.id')
	);
} else if (Configure::read('feature.manage_accounts')) {
	printf(__('If you don\'t already have an account, %s to get yourself set up.', true),
		$this->Html->link(__('follow these directions', true), array('controller' => 'users', 'action' => 'create_account'))
	);
} else {
	printf(__('This site manages user accounts through %s. If you don\'t already have an account, %s to get yourself set up.', true),
		Configure::read('feature.manage_name'), $this->Html->link(__('follow these directions', true), Configure::read('urls.register'))
	);
}
?></p>

<p><?php
if (!Configure::read('feature.auto_approve')) {
	__('Next, each person must have their completed profile approved by an administrator.');
}
echo ' ';
if ($is_logged_in) {
	if (!$this->UserCache->read('Person.complete')) {
		printf(__('To complete your profile, %s', true),
			$this->Html->link(__('follow these directions', true), array('controller' => 'people', 'action' => 'edit'))
		);
		$complete = __('and will not be until you have completed it', true);
	} else {
		$complete = __('but this should happen soon', true);
	}

	switch($this->UserCache->read('Person.status')) {
		case 'new':
			printf(__('Your profile has not yet been approved, %s. Until then, you can continue to use the site, but may be limited in some areas.', true), $complete);
			break;
		case 'active':
			__('Your profile has been approved, so you should be free to access all features of the site.');
			break;
		case 'inactive':
		case 'locked':
			printf(__('Your profile is currently %s, so you can continue to use the site, but may be limited in some areas. To have this changed, contact %s.', true),
				__($this->UserCache->read('Person.status'), true),
				$this->Html->link(Configure::read('email.admin_name'), 'mailto:' . Configure::read('email.admin_email'))
			);
			break;
	}
} else {
	__('After you have created your account and completed your profile, it is normally approved within one business day, often sooner, but you can use most features of the site while you are waiting for this.');
}
?></p>

<?php if (Configure::read('feature.registration')): ?>
<h2>Registration</h2>
<?php
	echo $this->element('help/topics', array(
			'section' => 'registration',
			'topics' => array(
				'introduction',
				'wizard',
			),
			'compact' => true,
	));
endif;
?>
<h2>Teams</h2>
<?php
echo $this->element('help/topics', array(
		'section' => 'teams',
		'topics' => array(
			'joining_teams',
			'my_teams',
		),
		'compact' => true,
));
?>
