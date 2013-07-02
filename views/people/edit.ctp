<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb ($this->Form->value('Person.first_name') . ' ' . $this->Form->value('Person.last_name'));
$this->Html->addCrumb (__('Edit', true));

$short = Configure::read('organization.short_name');
$admin = Configure::read('email.admin_email');

$access = array(1);
if ($is_admin) {
	$access[] = 2;
}

// TODO: Handle more than one sport in a site
$sport = array_shift(array_keys(Configure::read('options.sport')));
?>

<div class="people form">
<h2><?php
if (!empty($this->data['Upload']) && $this->data['Upload']['approved'] == true) {
	echo $this->element('people/player_photo', array('person' => $this->data['Person'], 'upload' => $this->data['Upload']));
}
echo $is_me ? __('Edit Your Profile', true) : "{$this->data['Person']['first_name']} {$this->data['Person']['last_name']}"; ?></h2>
<p>Note that email and phone publish settings below only apply to regular players. Captains will always have access to view the phone numbers and email addresses of their confirmed players. All Team Captains will also have their email address viewable by other players.</p>
<?php if (Configure::read('urls.privacy_policy')): ?>
<p>If you have concerns about the data <?php echo $short; ?> collects, please see our <strong><a href="<?php echo Configure::read('urls.privacy_policy'); ?>" target="_new">Privacy Policy</a>.</strong></p>
<?php endif; ?>

<?php if ($is_me && empty($this->data['Upload'])): ?>
	<fieldset>
 		<legend><?php __('Photo'); ?></legend>
<?php echo $this->ZuluruHtml->icon('blank_profile.jpg', array('class' => 'thumbnail', 'style' => 'float: left; margin-bottom: 7px;')); ?>
		<div style="float: left;">
<?php
	echo $this->Form->create(false, array('url' => array('action' => 'photo_upload', 'return' => true), 'enctype' => 'multipart/form-data'));
	echo $this->Form->input('image', array('type' => 'file', 'label' => __('Profile Photo', true)));
	echo $this->Form->end(array('label' => __('Upload', true), 'div' => false));
?>
		</div>
		<div class="clear"></div>
<?php echo $this->element('people/photo_legal'); ?>
	</fieldset>
<?php endif; ?>

<?php echo $this->Form->create('Person', array('url' => Router::normalize($this->here)));?>
	<fieldset>
 		<legend><?php __('Identity'); ?></legend>
	<?php
		echo $this->Form->input('id');
		if (in_array (Configure::read('profile.first_name'), $access)) {
			echo $this->ZuluruForm->input('first_name', array(
				'after' => $this->Html->para (null, __('First (and, if desired, middle) name.', true)),
			));
		} else {
			echo $this->ZuluruForm->input('first_name', array(
				'disabled' => 'true',
				'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your new name to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
			));
		}
		if (in_array (Configure::read('profile.last_name'), $access)) {
			echo $this->ZuluruForm->input('last_name');
		} else {
			echo $this->ZuluruForm->input('last_name', array(
				'disabled' => 'true',
				'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your new name to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
			));
		}
		if (!Configure::read('feature.manage_accounts')) {
			$username_edit = sprintf (Configure::read('urls.username_edit'), $id);
			if ($username_edit) {
				$username_link = $this->Html->link (Configure::read('feature.manage_name') . ' ' . __('username', true), $username_edit);
				echo $this->ZuluruForm->input('user_name', array(
					'disabled' => 'true',
					'after' => $this->Html->para (null, __('To change this, edit your', true) . ' ' . $username_link),
					));
			} else {
				echo $this->ZuluruForm->input('user_name', array(
					'disabled' => 'true',
					'after' => $this->Html->para (null, __('To change this, please email your existing user name and preferred new user name to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.'),
				));
			}
		} else {
			echo $this->ZuluruForm->input('user_name');
		}
		if (in_array (Configure::read('profile.gender'), $access)) {
			echo $this->ZuluruForm->input('gender', array(
				'type' => 'select',
				'empty' => '---',
				'options' => Configure::read('options.gender'),
			));
		}
	?>
	</fieldset>
	<?php
	// We hide the affiliate selection if it's not enabled, for admins,
	// and for managers when only one affiliate is allowed. The latter
	// is to prevent managers from switching themselves to another
	// affiliate where they're not a manager.
	if (Configure::read('feature.affiliates') && !$is_admin &&
		(Configure::read('feature.multiple_affiliates') || !$is_manager)):
	?>
	<fieldset>
 		<legend><?php __('Affiliate'); ?></legend>
	<?php
		if (Configure::read('feature.multiple_affiliates')) {
			$after = __('Select all affiliates you are interested in.', true);
			if ($is_manager) {
				$after .= ' ' . sprintf(__('Note that affiliates you are already a manager of (%s) are not included here; this will remain unchanged.', true),
					implode(Set::extract('/Affiliate/name', $this->Session->read('Zuluru.ManagedAffiliates')), true));
			}
			echo $this->ZuluruForm->input('Affiliate', array(
				'label' => __('Affiliates', true),
				'after' => $this->Html->para (null, $after),
				'multiple' => 'checkbox',
			));
		} else {
			echo $this->ZuluruForm->input('Affiliate', array(
				'empty' => '---',
				'multiple' => false,
			));
		}
	?>
	</fieldset>
	<?php endif; ?>
	<fieldset>
 		<legend><?php __('Online Contact'); ?></legend>
	<?php
		if (!Configure::read('feature.manage_accounts')) {
			$profile_edit = sprintf (Configure::read('urls.profile_edit'), $id);
			$profile_link = $this->Html->link (Configure::read('feature.manage_name') . ' ' . __('profile', true), $profile_edit);
			echo $this->ZuluruForm->input('email', array(
				'disabled' => 'true',
				'after' => $this->Html->para (null, __('To change this, edit your', true) . ' ' . $profile_link),
			));
		} else {
			echo $this->ZuluruForm->input('email');
		}
		echo $this->ZuluruForm->input('publish_email', array(
			'label' => __('Allow other players to view my email address', true),
		));
	?>
	</fieldset>
	<?php if (Configure::read('profile.addr_street') || Configure::read('profile.addr_city') ||
				Configure::read('profile.addr_prov') || Configure::read('profile.addr_country') ||
				Configure::read('profile.addr_postalcode')): ?>
	<fieldset>
 		<legend><?php __('Street Address'); ?></legend>
	<?php
		if (in_array (Configure::read('profile.addr_street'), $access)) {
			echo $this->ZuluruForm->input('addr_street', array(
				'label' => __('Street and Number', true),
				'after' => $this->Html->para (null, __('Number, street name, and apartment number if necessary.', true)),
			));
		} else if (Configure::read('profile.addr_street')) {
			echo $this->ZuluruForm->input('addr_street', array(
				'disabled' => 'true',
				'label' => __('Street and Number', true),
				'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your new address to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
			));
		}
		if (in_array (Configure::read('profile.addr_city'), $access)) {
			echo $this->ZuluruForm->input('addr_city', array(
				'label' => __('City', true),
				'after' => $this->Html->para (null, __('Name of city.', true)),
			));
		} else if (Configure::read('profile.addr_city')) {
			echo $this->ZuluruForm->input('addr_city', array(
				'disabled' => 'true',
				'label' => __('City', true),
				'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your new address to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
			));
		}
		if (in_array (Configure::read('profile.addr_prov'), $access)) {
			echo $this->ZuluruForm->input('addr_prov', array(
				'label' => __('Province', true),
				'type' => 'select',
				'empty' => '---',
				'options' => $provinces,
				'after' => $this->Html->para (null, __('Select a province/state from the list', true)),
			));
		} else if (Configure::read('profile.addr_prov')) {
			echo $this->ZuluruForm->input('addr_prov', array(
				'disabled' => 'true',
				'label' => __('Province', true),
				'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your new address to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
			));
		}
		if (in_array (Configure::read('profile.addr_country'), $access)) {
			echo $this->ZuluruForm->input('addr_country', array(
				'label' => __('Country', true),
				'type' => 'select',
				'empty' => '---',
				'options' => $countries,
				'after' => $this->Html->para (null, __('Select a country from the list.', true)),
			));
		} else if (Configure::read('profile.addr_country')) {
			echo $this->ZuluruForm->input('addr_country', array(
				'disabled' => 'true',
				'label' => __('Country', true),
				'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your new address to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
			));
		}
		if (in_array (Configure::read('profile.addr_postalcode'), $access)) {
			$fields = __(Configure::read('ui.fields'), true);
			echo $this->ZuluruForm->input('addr_postalcode', array(
				'label' => __('Postal Code', true),
				'after' => $this->Html->para (null, sprintf(__("Please enter a correct postal code matching the address above. $short uses this information to help locate new %s near its members.", true), $fields)),
			));
		} else if (Configure::read('profile.addr_postalcode')) {
			echo $this->ZuluruForm->input('addr_postalcode', array(
				'disabled' => 'true',
				'label' => __('Postal Code', true),
				'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your new address to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
			));
		}
	?>
	</fieldset>
	<?php endif; ?>
	<?php if (Configure::read('profile.home_phone') || Configure::read('profile.work_phone') ||
				Configure::read('profile.mobile_phone')): ?>
	<fieldset>
 		<legend><?php
 		if (Configure::read('profile.home_phone') + Configure::read('profile.work_phone') +
 			Configure::read('profile.mobile_phone') > 1)
 		{
 			$number = 'Numbers';
 		} else {
 			$number = 'Number';
 		}
		__("Telephone $number");
		?></legend>
	<?php
		if (in_array (Configure::read('profile.home_phone'), $access)) {
			echo $this->ZuluruForm->input('home_phone', array(
				'after' => $this->Html->para (null, __('Enter your home telephone number. If you have only a mobile phone, enter that number both here and below.', true)),
			));
		} else if (Configure::read('profile.home_phone')) {
			echo $this->ZuluruForm->input('home_phone', array(
				'disabled' => 'true',
				'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your new phone number to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
			));
		}
		if (Configure::read('profile.home_phone')) {
			echo $this->ZuluruForm->input('publish_home_phone', array(
				'label' => __('Allow other players to view home number', true),
			));
		}
		if (in_array (Configure::read('profile.work_phone'), $access)) {
			echo $this->ZuluruForm->input('work_phone', array(
				'after' => $this->Html->para (null, __('Enter your work telephone number (optional).', true)),
			));
			echo $this->ZuluruForm->input('work_ext', array(
				'label' => 'Work Extension',
				'after' => $this->Html->para (null, __('Enter your work extension (optional).', true)),
			));
		} else if (Configure::read('profile.work_phone')) {
			echo $this->ZuluruForm->input('work_phone', array(
				'disabled' => 'true',
			));
			echo $this->ZuluruForm->input('work_ext', array(
				'disabled' => 'true',
				'label' => 'Work Extension',
				'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your new phone number to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
			));
		}
		if (Configure::read('profile.work_phone')) {
			echo $this->ZuluruForm->input('publish_work_phone', array(
				'label' => __('Allow other players to view work number', true),
			));
		}
		if (in_array (Configure::read('profile.mobile_phone'), $access)) {
			echo $this->ZuluruForm->input('mobile_phone', array(
				'after' => $this->Html->para (null, __('Enter your cell or pager number (optional).', true)),
			));
		} else if (Configure::read('profile.mobile_phone')) {
			echo $this->ZuluruForm->input('mobile_phone', array(
				'disabled' => 'true',
				'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your new phone number to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
			));
		}
		if (Configure::read('profile.mobile_phone')) {
			echo $this->ZuluruForm->input('publish_mobile_phone', array(
				'label' => __('Allow other players to view mobile number', true),
			));
		}
	?>
	</fieldset>
	<?php endif; ?>
	<?php if ($is_admin) : ?>
	<fieldset>
 		<legend><?php __('Account Information'); ?></legend>
	<?php
		echo $this->ZuluruForm->input('group_id', array(
			'label' => __('Account Type', true),
			'type' => 'select',
			'empty' => '---',
			'options' => $groups,
		));
		echo $this->ZuluruForm->input('status', array(
			'type' => 'select',
			'empty' => '---',
			'options' => Configure::read('options.record_status'),
		));
	?>
	</fieldset>
	<?php endif; ?>
	<?php if (Configure::read('profile.skill_level') || Configure::read('profile.year_started') ||
				Configure::read('profile.birthdate') || Configure::read('profile.height') ||
				Configure::read('profile.shirt_size') || Configure::read('feature.dog_questions') ||
				in_array(Configure::read('profile.willing_to_volunteer'), $access) ||
				in_array(Configure::read('profile.contact_for_feedback'), $access)): ?>
	<fieldset>
 		<legend><?php __('Player Information'); ?></legend>
	<?php
		if (in_array (Configure::read('profile.skill_level'), $access)) {
			if (Configure::read('sport.rating_questions')) {
				$after = $this->Html->para(null, __('Please use the questionnaire to ', true) . $this->Html->link (__('calculate your rating', true), '#', array('onclick' => 'dorating(); return false;')) . '.');
			} else {
				$after = null;
			}
			echo $this->ZuluruForm->input('skill_level', array(
				'type' => 'select',
				'empty' => '---',
				'options' => Configure::read('options.skill'),
				'after' => $after,
			));
		} else if (Configure::read('profile.skill_level')) {
			echo $this->ZuluruForm->input('skill_level', array(
				'disabled' => 'true',
				'value' => Configure::read("options.skill.{$this->data['Person']['skill_level']}"),
				'size' => 70,
				'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your new skill level to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
			));
		}
		if (in_array (Configure::read('profile.year_started'), $access)) {
			echo $this->ZuluruForm->input('year_started', array(
				'type' => 'select',
				'options' => $this->Form->__generateOptions('year', array(
						'min' => Configure::read('options.year.started.min'),
						'max' => Configure::read('options.year.started.max'),
						'order' => 'desc'
				)),
				'empty' => '---',
				'after' => $this->Html->para(null, 'The year you started playing in <strong>this</strong> league.'),
			));
		} else if (Configure::read('profile.year_started')) {
			echo $this->ZuluruForm->input('year_started', array(
				'disabled' => 'true',
				'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your correct year started to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
			));
		}
		if (in_array (Configure::read('profile.birthdate'), $access)) {
			echo $this->ZuluruForm->input('birthdate', array(
				'minYear' => Configure::read('options.year.born.min'),
				'maxYear' => Configure::read('options.year.born.max'),
				'after' => $this->Html->para(null, __('Please enter a correct birthdate; having accurate information is important for insurance purposes.', true)),
			));
		} else if (Configure::read('profile.birthdate')) {
			echo $this->ZuluruForm->input('birthdate', array(
				'disabled' => 'true',
				'minYear' => Configure::read('options.year.born.min'),
				'maxYear' => Configure::read('options.year.born.max'),
				'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your correct birthdate to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
			));
		}
		if (in_array (Configure::read('profile.height'), $access)) {
			echo $this->ZuluruForm->input('height', array(
				'size' => 6,
				'after' => $this->Html->para(null, __('Please enter your height in inches (5 feet is 60 inches; 6 feet is 72 inches). This is used to help generate even teams for hat leagues.', true)),
			));
		} else if (Configure::read('profile.height')) {
			echo $this->ZuluruForm->input('height', array(
				'disabled' => 'true',
				'size' => 6,
				'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your new height to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
			));
		}
		if (in_array (Configure::read('profile.shirt_size'), $access)) {
			echo $this->ZuluruForm->input('shirt_size', array(
				'type' => 'select',
				'empty' => '---',
				'options' => Configure::read('options.shirt_size'),
			));
		} else if (Configure::read('profile.shirt_size')) {
			echo $this->ZuluruForm->input('shirt_size', array(
				'disabled' => 'true',
				'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your new shirt size to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
			));
		}
		if (Configure::read('feature.dog_questions')) {
			echo $this->ZuluruForm->input('has_dog');
		}
		if (in_array (Configure::read('profile.willing_to_volunteer'), $access)) {
			echo $this->ZuluruForm->input('willing_to_volunteer', array(
				'label' => __("Can $short contact you about volunteering?", true),
			));
		}
		if (in_array (Configure::read('profile.contact_for_feedback'), $access)) {
			echo $this->ZuluruForm->input('contact_for_feedback', array(
				'label' => __("From time to time, $short would like to contact members with information on our programs and to solicit feedback. Can $short contact you in this regard?"),
			));
		}
	?>
	</fieldset>
	<?php endif; ?>
<?php echo $this->Form->end(__('Submit', true));?>
</div>

<?php
if (Configure::read('profile.skill_level') && Configure::read('sport.rating_questions')) {
	echo $this->element('people/rating', array('sport' => $sport, 'field' => '#PersonSkillLevel'));
}
?>
