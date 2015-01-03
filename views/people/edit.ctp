<?php
$cached = $this->UserCache->read('Person', $this->Form->value('Person.id'));
$this->Html->addCrumb (__('People', true));
$this->Html->addCrumb (array_key_exists('first_name', $this->data['Person']) ? "{$this->data['Person']['first_name']} {$this->data['Person']['last_name']}" : "{$cached['first_name']} {$cached['last_name']}");
$this->Html->addCrumb (__('Edit', true));

$short = Configure::read('organization.short_name');
$admin = Configure::read('email.admin_email');
$this_is_player = (!empty($cached['Group']) && Set::extract('/GroupsPerson[group_id=2]', $cached['Group']));
$this_is_player = (!empty($this_is_player));

$access = array(1);
// People with incomplete profiles can update any of the fields that
// normally only admins can edit, so that they can successfully fill
// out all of the profile.
if ($is_admin || !$cached['complete']) {
	$access[] = 2;
}

// TODO: Handle more than one sport in a site
$sport = reset(array_keys(Configure::read('options.sport')));
?>

<div class="people form">
<h2><?php
if (!empty($this->data['Upload']) && $this->data['Upload']['approved'] == true) {
	echo $this->element('people/player_photo', array('person' => (array_key_exists('first_name', $this->data['Person']) ? $this->data['Person'] : $cached), 'photo' => $this->data));
}
echo $is_me ? __('Edit Your Profile', true) : (array_key_exists('first_name', $this->data['Person']) ? "{$this->data['Person']['first_name']} {$this->data['Person']['last_name']}" : "{$cached['first_name']} {$cached['last_name']}"); ?></h2>
<?php if ($cached['user_id']): ?>
<p><?php __('Note that email and phone publish settings below only apply to regular people. Coaches and captains will always have access to view the phone numbers and email addresses of their confirmed players. All team coaches and captains will also have their email address viewable by other players.'); ?></p>
	<?php if (Configure::read('urls.privacy_policy')): ?>
<p><?php printf(__('If you have concerns about the data %s collects, please see our %s.', true),
		$short,
		$this->Html->tag('strong', $this->Html->link(__('Privacy Policy', true), Configure::read('urls.privacy_policy'), array('target' => '_new')))
);
?></p>
	<?php endif; ?>

	<?php if (Configure::read('feature.photos') && $is_me && empty($this->data['Upload'])): ?>
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
<?php endif; ?>

<?php echo $this->Form->create('Person', array('url' => Router::normalize($this->here)));?>
	<?php if ($cached['user_id'] || $is_admin || $is_manager): ?>
	<fieldset>
		<legend><?php __('Account Type'); ?></legend>
	<?php
	if ($cached['user_id']) {
		echo $this->ZuluruForm->input('Group.Group', array(
			'label' => __('Select all roles that apply to you.', true),
			'type' => 'select',
			'multiple' => 'checkbox',
			'options' => $groups,
			'hide_single' => true,
		));
	}
	if ($is_admin || $is_manager) {
		echo $this->ZuluruForm->input('status', array(
			'type' => 'select',
			'empty' => '---',
			'options' => Configure::read('options.record_status'),
		));
	}
	?>
	</fieldset>
	<?php endif; ?>
	<fieldset>
		<legend><?php __('Your Information'); ?></legend>
		<div style="float:left;">
	<?php
	echo $this->Form->input('id');
	if (in_array (Configure::read('profile.first_name'), $access)) {
		echo $this->ZuluruForm->input('first_name', array(
			'after' => $this->Html->para (null, __('First (and, if desired, middle) name.', true)),
		));
	} else {
		echo $this->ZuluruForm->input('first_name', array(
			'value' => $cached['first_name'],
			'disabled' => 'true',
			'class' => 'disabled',
			'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your new name to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
		));
	}
	if (in_array (Configure::read('profile.last_name'), $access)) {
		echo $this->ZuluruForm->input('last_name');
	} else {
		echo $this->ZuluruForm->input('last_name', array(
			'value' => $cached['last_name'],
			'disabled' => 'true',
			'class' => 'disabled',
			'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your new name to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
		));
	}

	if ($cached['user_id']) {
		$phone_numbers_enabled = array_diff(array(
			Configure::read('profile.home_phone'),
			Configure::read('profile.work_phone'),
			Configure::read('profile.mobile_phone')
		), array(0));
		if (count($phone_numbers_enabled) > 1) {
			echo $this->Html->para (null, __('Enter at least one telephone number below.', true));
		}

		if (in_array (Configure::read('profile.home_phone'), $access)) {
			echo $this->ZuluruForm->input('home_phone', array(
				'after' => $this->Html->para (null, __('Enter your home telephone number.', true)),
			));
		} else if (Configure::read('profile.home_phone')) {
			echo $this->ZuluruForm->input('home_phone', array(
				'value' => $cached['home_phone'],
				'disabled' => 'true',
				'class' => 'disabled',
				'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your new phone number to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
			));
		}
		if (Configure::read('profile.home_phone')) {
			echo $this->ZuluruForm->input('publish_home_phone', array(
				'label' => __('Allow other people to view home number', true),
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
				'value' => $cached['work_phone'],
				'disabled' => 'true',
				'class' => 'disabled',
			));
			echo $this->ZuluruForm->input('work_ext', array(
				'value' => $cached['work_ext'],
				'disabled' => 'true',
				'class' => 'disabled',
				'label' => 'Work Extension',
				'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your new phone number to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
			));
		}
		if (Configure::read('profile.work_phone')) {
			echo $this->ZuluruForm->input('publish_work_phone', array(
				'label' => __('Allow other people to view work number', true),
			));
		}
		if (in_array (Configure::read('profile.mobile_phone'), $access)) {
			echo $this->ZuluruForm->input('mobile_phone', array(
				'after' => $this->Html->para (null, __('Enter your cell or pager number (optional).', true)),
			));
		} else if (Configure::read('profile.mobile_phone')) {
			echo $this->ZuluruForm->input('mobile_phone', array(
				'value' => $cached['mobile_phone'],
				'disabled' => 'true',
				'class' => 'disabled',
				'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your new phone number to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
			));
		}
		if (Configure::read('profile.mobile_phone')) {
			echo $this->ZuluruForm->input('publish_mobile_phone', array(
				'label' => __('Allow other people to view mobile number', true),
			));
		}
	}
	?>
		</div>
	<?php if ($cached['user_id']): ?>
	<fieldset class="parent" style="display:none; float:left">
		<legend><?php __('Alternate Contact (optional)'); ?></legend>
		<p style="max-width:18em;">This alternate contact information is for display purposes only. If the alternate contact should have their own login details, do not enter their information here; instead create a separate account and then link them together.</p>
	<?php
		echo $this->ZuluruForm->input('alternate_first_name', array(
			'label' => 'First Name',
			'after' => $this->Html->para (null, __('First (and, if desired, middle) name.', true)),
		));
		echo $this->ZuluruForm->input('alternate_last_name', array(
			'label' => 'Last Name',
		));
		if (Configure::read('profile.work_phone')) {
			echo $this->ZuluruForm->input('alternate_work_phone', array(
				'label' => 'Work Phone',
				'after' => $this->Html->para (null, __('Enter your work telephone number (optional).', true)),
			));
			echo $this->ZuluruForm->input('alternate_work_ext', array(
				'label' => 'Work Extension',
				'after' => $this->Html->para (null, __('Enter your work extension (optional).', true)),
			));
			echo $this->ZuluruForm->input('publish_alternate_work_phone', array(
				'label' => __('Allow other people to view work number', true),
			));
		}
		if (Configure::read('profile.mobile_phone')) {
			echo $this->ZuluruForm->input('alternate_mobile_phone', array(
				'label' => 'Mobile Phone',
				'after' => $this->Html->para (null, __('Enter your cell or pager number (optional).', true)),
			));
			echo $this->ZuluruForm->input('publish_alternate_mobile_phone', array(
				'label' => __('Allow other people to view mobile number', true),
			));
		}
	?>
	</fieldset>
	<?php endif; ?>
	</fieldset>
	<?php if ($cached['user_id']): ?>
	<fieldset>
		<legend><?php __('User Name'); ?></legend>
	<?php
		echo $this->ZuluruForm->hidden("$user_model.$id_field");
		echo $this->ZuluruForm->input("$user_model.$user_field", array(
			'label' => __('User Name', true),
		));
	?>
	</fieldset>
	<?php endif; ?>
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
					implode(Set::extract('/Affiliate/name', $this->UserCache->read('ManagedAffiliates')), true));
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
	<?php if ($cached['user_id']): ?>
	<fieldset>
		<legend><?php __('Online Contact'); ?></legend>
		<?php
		echo $this->ZuluruForm->input("$user_model.$email_field", array(
			'label' => __('Email', true),
		));
		echo $this->ZuluruForm->input('publish_email', array(
			'label' => __('Allow other people to view my email address', true),
		));
		echo $this->ZuluruForm->input('alternate_email');
		echo $this->ZuluruForm->input('publish_alternate_email', array(
			'label' => __('Allow other people to view my alternate email address', true),
		));
		if (Configure::read('feature.gravatar')) {
			if (Configure::read('feature.photos')) {
				$after = sprintf(__('You can have an image shown on your account by uploading a photo directly, or by enabling this setting and then create a <a href="http://www.gravatar.com">gravatar.com</a> account using the email address you\'ve associated with your %s account.', true), Configure::read('organization.short_name'));
			} else {
				$after = sprintf(__('You can have an image shown on your account if you enable this setting and then create a <a href="http://www.gravatar.com">gravatar.com</a> account using the email address you\'ve associated with your %s account.', true), Configure::read('organization.short_name'));
			}
			echo $this->ZuluruForm->input('show_gravatar', array(
				'label' => __('Show Gravatar image for your account?', true),
				'after' => $this->Html->para (null, $after),
			));
		}
		if (in_array (Configure::read('profile.contact_for_feedback'), $access)) {
			echo $this->ZuluruForm->input('contact_for_feedback', array(
				'label' => sprintf(__('From time to time, %s would like to contact members with information on our programs and to solicit feedback. Can %s contact you in this regard?', true), $short, $short),
			));
		}
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
				'value' => $cached['addr_street'],
				'disabled' => 'true',
				'class' => 'disabled',
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
				'value' => $cached['addr_city'],
				'disabled' => 'true',
				'class' => 'disabled',
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
				'value' => $cached['addr_prov'],
				'disabled' => 'true',
				'class' => 'disabled',
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
				'hide_single' => true,
				'after' => $this->Html->para (null, __('Select a country from the list.', true)),
			));
		} else if (Configure::read('profile.addr_country') && count($countries) > 1) {
			echo $this->ZuluruForm->input('addr_country', array(
				'value' => $cached['addr_country'],
				'disabled' => 'true',
				'class' => 'disabled',
				'label' => __('Country', true),
				'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your new address to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
			));
		}
		if (in_array (Configure::read('profile.addr_postalcode'), $access)) {
			$fields = __(Configure::read('ui.fields'), true);
			echo $this->ZuluruForm->input('addr_postalcode', array(
				'label' => __('Postal Code', true),
				'after' => $this->Html->para (null, sprintf(__('Please enter a correct postal code matching the address above. %s uses this information to help locate new %s near its members.', true), $short, __(Configure::read('ui.fields'), true))),
			));
		} else if (Configure::read('profile.addr_postalcode')) {
			echo $this->ZuluruForm->input('addr_postalcode', array(
				'value' => $cached['addr_postalcode'],
				'disabled' => 'true',
				'class' => 'disabled',
				'label' => __('Postal Code', true),
				'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your new address to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
			));
		}
		?>
	</fieldset>
		<?php endif; ?>
	<?php endif; ?>
	<?php if (Configure::read('profile.gender') || Configure::read('profile.birthdate') ||
				Configure::read('profile.year_started') || Configure::read('profile.skill_level') ||
				Configure::read('profile.height') || Configure::read('profile.shirt_size') ||
				Configure::read('feature.dog_questions')): ?>
	<fieldset class="player" <?php if ($cached['user_id'] || !$this_is_player): ?>style="display:none;"<?php endif; ?>>
		<legend><?php __('Your Player Profile'); ?></legend>
	<?php
		if (in_array (Configure::read('profile.gender'), $access)) {
			echo $this->ZuluruForm->input('gender', array(
				'type' => 'select',
				'empty' => '---',
				'options' => Configure::read('options.gender'),
			));
		} else {
			echo $this->ZuluruForm->input('gender', array(
				'value' => $cached['gender'],
				'disabled' => 'true',
				'class' => 'disabled',
				'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your gender to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
			));
		}
		if (in_array (Configure::read('profile.birthdate'), $access)) {
			if (Configure::read('feature.birth_year_only')) {
				echo $this->ZuluruForm->input('birthdate', array(
					'dateFormat' => 'Y',
					'minYear' => Configure::read('options.year.born.min'),
					'maxYear' => Configure::read('options.year.born.max'),
					'empty' => '---',
					'after' => $this->Html->para(null, __('Please enter a correct birthdate; having accurate information is important for insurance purposes.', true)),
				));
				echo $this->Form->hidden('birthdate.month', array('value' => 1));
				echo $this->Form->hidden('birthdate.day', array('value' => 1));
			} else {
				echo $this->ZuluruForm->input('birthdate', array(
					'minYear' => Configure::read('options.year.born.min'),
					'maxYear' => Configure::read('options.year.born.max'),
					'empty' => '---',
					'after' => $this->Html->para(null, __('Please enter a correct birthdate; having accurate information is important for insurance purposes.', true)),
				));
			}
		} else if (Configure::read('profile.birthdate')) {
			echo $this->ZuluruForm->input('birthdate', array(
				'value' => $cached['birthdate'],
				'disabled' => 'true',
				'class' => 'disabled',
				'minYear' => Configure::read('options.year.born.min'),
				'maxYear' => Configure::read('options.year.born.max'),
				'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your correct birthdate to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
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
				'value' => $cached['year_started'],
				'disabled' => 'true',
				'class' => 'disabled',
				'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your correct year started to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
			));
		}
		if (in_array (Configure::read('profile.skill_level'), $access)) {
			if (Configure::read('sport.rating_questions')) {
				$after = $this->Html->para(null, __('Please use the questionnaire to ', true) . $this->Html->link (__('calculate your rating', true), '#', array('onclick' => 'dorating("#PersonSkillLevel"); return false;')) . '.');
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
				'value' => Configure::read("options.skill.{$cached['skill_level']}"),
				'disabled' => 'true',
				'class' => 'disabled',
				'size' => 70,
				'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your new skill level to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
			));
		}
		if (in_array (Configure::read('profile.height'), $access)) {
			if (Configure::read('feature.units') == 'Metric') {
				$units = __('centimeters', true);
			} else {
				$units = __('inches (5 feet is 60 inches; 6 feet is 72 inches)', true);
			}
			echo $this->ZuluruForm->input('height', array(
				'size' => 6,
				'after' => $this->Html->para(null, sprintf(__('Please enter your height in %s. This is used to help generate even teams for hat leagues.', true), $units)),
			));
		} else if (Configure::read('profile.height')) {
			echo $this->ZuluruForm->input('height', array(
				'value' => $cached['height'],
				'disabled' => 'true',
				'class' => 'disabled',
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
				'value' => $cached['shirt_size'],
				'disabled' => 'true',
				'class' => 'disabled',
				'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your new shirt size to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
			));
		}
		if (Configure::read('feature.dog_questions')) {
			echo $this->ZuluruForm->input('has_dog');
		}
	?>
	</fieldset>
	<?php endif; ?>
<?php echo $this->Form->end(__('Submit', true));?>
</div>

<?php
if ($this_is_player && Configure::read('profile.skill_level') && Configure::read('sport.rating_questions')) {
	echo $this->element('people/rating', array('sport' => $sport));
}

// Handle changes to parent and player checkboxes
if ($cached['user_id']) {
	$this->Js->get('#GroupGroup1')->event('change', 'parentChanged();');
	$this->Js->get('#GroupGroup2')->event('change', 'playerChanged();');
	echo $this->Html->scriptBlock('
function parentChanged() {
	var checked = jQuery("#GroupGroup1").prop("checked");
	if (checked) {
		jQuery(".parent").css("display", "");
		jQuery(".parent input, .parent select").not(".disabled").removeAttr("disabled");
	} else {
		jQuery(".parent").css("display", "none");
		jQuery(".parent input, .parent select").attr("disabled", "disabled");
	}
}

function playerChanged() {
	var checked = jQuery("#GroupGroup2").prop("checked");
	if (checked) {
		jQuery(".player").css("display", "");
		jQuery(".player input, .player select").not(".disabled").removeAttr("disabled");
	} else {
		jQuery(".player").css("display", "none");
		jQuery(".player input, .player select").attr("disabled", "disabled");
	}
}
	');
	$this->Js->buffer('parentChanged(); playerChanged();');
}
?>
