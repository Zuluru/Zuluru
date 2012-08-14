<h2>New User Guide</h2>
<p>For a new user, <?php echo ZULURU; ?> can be a little overwhelming.
This guide will help you through the most important things to get you started.
After that, you may be interested in the <?php
echo $this->Html->link (__('advanced users guide', true), array('controller' => 'help', 'action' => 'guide', 'advanced')); ?>, and if you plan on running a team, the <?php
echo $this->Html->link (__('captains guide', true), array('controller' => 'help', 'action' => 'guide', 'captain')); ?> is a useful resource.</p>

<h2>User Account and Profile</h2>
<p>Some features of <?php echo ZULURU; ?> (e.g. schedules and standings) are available for anyone to use.
However, to participate in the <?php echo Configure::read('organization.name'); ?>, you must have a user account on the system.
<?php if ($is_logged_in): ?>
You are already logged in to the system, so it seems that you've successfully taken care of this step. For the record, your user name is '<?php echo $this->Session->read('Zuluru.Person.user_name'); ?>' and your ID number is <?php echo $this->Session->read('Zuluru.Person.id'); ?>.
<?php elseif (Configure::read('feature.manage_accounts')): ?>
If you don't already have an account, <?php echo $this->Html->link('follow these directions', array('controller' => 'users', 'action' => 'create_account')); ?> to get yourself set up.
<?php else: ?>
This site manages user accounts through <?php echo Configure::read('feature.manage_name'); ?>. If you don't already have an account, <?php echo $this->Html->link('follow these directions', Configure::read('urls.register')); ?> to get yourself set up.
<?php endif; ?></p>
<p>Next, each player must have their completed profile approved by an administrator.
<?php if ($is_logged_in): ?>
<?php if (!$this->Session->read('Zuluru.Person.complete')): ?>
To complete your profile, <?php echo $this->Html->link('follow these directions', array('controller' => 'people', 'action' => 'edit')); ?>.
<?php
	$complete = 'and will not be until you have completed it';
else:
	$complete = 'but this should happen soon';
endif;

switch($this->Session->read('Zuluru.Person.status')) {
	case 'new':
		echo "Your profile has not yet been approved, $complete. Until then, you can continue to use the site, but may be limited in some areas.";
		break;
	case 'active':
		echo 'Your profile has been approved, so you should be free to access all features of the site.';
		break;
	case 'inactive':
	case 'locked':
		echo 'Your profile is currently ' . $this->Session->read('Zuluru.Person.status') . ', so you can continue to use the site, but may be limited in some areas.' . 
			' To have this changed, contact ' . $this->Html->link(Configure::read('email.admin_name'), 'mailto:' . Configure::read('email.admin_email')) . '.';
		break;
}
?>
<?php else: ?>
After you have created your account and completed your profile, it is normally approved within one business day, often sooner, but you can use most features of the site while you are waiting for this.
<?php endif; ?></p>

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
