<?php
$this->Html->addCrumb (__('Users', true));
$this->Html->addCrumb (__('Create', true));

$short = Configure::read('organization.short_name');
?>

<p><?php
__('To create a new account, fill in all the fields below and click \'Submit\' when done.');
if (!Configure::read('feature.auto_approve')) {
	echo ' ' . __('Your account will be placed on hold until approved by an administrator. Once approved, you will have full access to the system.', true);
}
?></p>
<p><?php printf(__('%s If you already have an account from a previous season, %s! Instead, please %s to regain access to your account.', true),
		$this->Html->tag('strong', __('NOTE', true) . ': '),
		$this->Html->tag('strong', __('DO NOT CREATE ANOTHER ONE', true)),
		$this->Html->link(__('follow these instructions', true), array('controller' => 'users', 'action' => 'reset_password'))
);
?></p>
<p><?php __('Note that email and phone publish settings below only apply to regular people. Coaches and captains will always have access to view the phone numbers and email addresses of their confirmed players. All team coaches and captains will also have their email address viewable by other players.'); ?></p>
<?php if (Configure::read('urls.privacy_policy')): ?>
<p><?php printf(__('If you have concerns about the data %s collects, please see our %s.', true),
		$short,
		$this->Html->tag('strong', $this->Html->link(__('Privacy Policy', true), Configure::read('urls.privacy_policy'), array('target' => '_new')))
);
?></p>
<?php endif; ?>

<div class="users form">
<?php
// Create the form and maybe add some spam-prevention tools
echo $this->Form->create($user_model, array('url' => Router::normalize($this->here)));
if (Configure::read('feature.antispam')):
?>
	<div id="spam_trap" style="display:none;">
<?php
	echo $this->ZuluruForm->input('subject');
	echo $this->ZuluruForm->hidden('timestamp', array('default' => time()));
?>
	</div>
<?php endif; ?>

	<fieldset>
		<legend><?php __('Account Type'); ?></legend>
	<?php
		echo $this->ZuluruForm->input('Group.Group', array(
			'label' => __('Select all roles that apply to you.', true) . ' ' . __('You will be able to change these later, if required.', true),
			'type' => 'select',
			'multiple' => 'checkbox',
			'options' => $groups,
			'hide_single' => true,
		));
		if ($is_admin || $is_manager) {
			$options = Configure::read('options.record_status');
			if (Configure::read('feature.auto_approve')) {
				unset($options['new']);
			}
			echo $this->ZuluruForm->input('Person.0.status', array(
				'type' => 'select',
				'empty' => '---',
				'options' => $options,
			));
		}
	?>
	</fieldset>
	<fieldset>
		<legend><?php __('Your Information'); ?></legend>
		<div style="float:left;">
	<?php
		echo $this->ZuluruForm->input('Person.0.first_name', array(
			'after' => $this->Html->para (null, __('First (and, if desired, middle) name.', true)),
		));
		echo $this->ZuluruForm->input('Person.0.last_name');

		$phone_numbers_enabled = array_diff(array(
			Configure::read('profile.home_phone'),
			Configure::read('profile.work_phone'),
			Configure::read('profile.mobile_phone')
		), array(0));
		if (count($phone_numbers_enabled) > 1) {
			echo $this->Html->para (null, __('Enter at least one telephone number below.', true));
		}

		if (Configure::read('profile.home_phone')) {
			echo $this->ZuluruForm->input('Person.0.home_phone', array(
				'after' => $this->Html->para (null, __('Enter your home telephone number.', true)),
			));
			echo $this->ZuluruForm->input('Person.0.publish_home_phone', array(
				'label' => __('Allow other people to view home number', true),
			));
		}
		if (Configure::read('profile.work_phone')) {
			echo $this->ZuluruForm->input('Person.0.work_phone', array(
				'after' => $this->Html->para (null, __('Enter your work telephone number (optional).', true)),
			));
			echo $this->ZuluruForm->input('Person.0.work_ext', array(
				'label' => 'Work Extension',
				'after' => $this->Html->para (null, __('Enter your work extension (optional).', true)),
			));
			echo $this->ZuluruForm->input('Person.0.publish_work_phone', array(
				'label' => __('Allow other people to view work number', true),
			));
		}
		if (Configure::read('profile.mobile_phone')) {
			echo $this->ZuluruForm->input('Person.0.mobile_phone', array(
				'after' => $this->Html->para (null, __('Enter your cell or pager number (optional).', true)),
			));
			echo $this->ZuluruForm->input('Person.0.publish_mobile_phone', array(
				'label' => __('Allow other people to view mobile number', true),
			));
		}
	?>
		</div>
	<fieldset class="parent" style="display:none; float:left">
		<legend><?php __('Alternate Contact (optional)'); ?></legend>
		<p style="max-width:18em;"><?php __('This alternate parent/guardian contact information is for display purposes only. If the alternate contact should have their own login, do not enter their information here; instead create a separate account and then link them together.'); ?></p>
		<p style="max-width:18em;"><?php __('This is not for your child\'s name; enter that in the "Child Profile" section below.'); ?></p>
	<?php
		echo $this->ZuluruForm->input('Person.0.alternate_first_name', array(
			'label' => __('First Name', true),
			'after' => $this->Html->para (null, __('First (and, if desired, middle) name.', true)),
		));
		echo $this->ZuluruForm->input('Person.0.alternate_last_name', array(
			'label' => __('Last Name', true),
		));
		if (Configure::read('profile.work_phone')) {
			echo $this->ZuluruForm->input('Person.0.alternate_work_phone', array(
				'label' => __('Work Phone', true),
				'after' => $this->Html->para (null, __('Enter your work telephone number (optional).', true)),
			));
			echo $this->ZuluruForm->input('Person.0.alternate_work_ext', array(
				'label' => __('Work Extension', true),
				'after' => $this->Html->para (null, __('Enter your work extension (optional).', true)),
			));
			echo $this->ZuluruForm->input('Person.0.publish_alternate_work_phone', array(
				'label' => __('Allow other people to view work number', true),
			));
		}
		if (Configure::read('profile.mobile_phone')) {
			echo $this->ZuluruForm->input('Person.0.alternate_mobile_phone', array(
				'label' => __('Mobile Phone', true),
				'after' => $this->Html->para (null, __('Enter your cell or pager number (optional).', true)),
			));
			echo $this->ZuluruForm->input('Person.0.publish_alternate_mobile_phone', array(
				'label' => __('Allow other people to view mobile number', true),
			));
		}
	?>
	</fieldset>
	</fieldset>
	<fieldset>
		<legend><?php __('User Name and Password'); ?></legend>
	<?php
		echo $this->ZuluruForm->input($user_field, array(
			'label' => __('User Name', true),
		));
		echo $this->ZuluruForm->input('passwd', array('type' => 'password', 'label' => __('Password', true)));
		echo $this->ZuluruForm->input('confirm_passwd', array('type' => 'password', 'label' => __('Confirm Password', true)));
	?>
	</fieldset>
	<?php if (Configure::read('feature.affiliates')): ?>
	<fieldset>
		<legend><?php __('Affiliate'); ?></legend>
	<?php
		if (Configure::read('feature.multiple_affiliates')) {
			echo $this->ZuluruForm->input('Affiliate', array(
				'label' => __('Affiliates', true),
				'after' => $this->Html->para (null, __('Select all affiliates you are interested in.', true)),
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
		echo $this->ZuluruForm->input($email_field);
		echo $this->ZuluruForm->input('Person.0.publish_email', array(
			'label' => __('Allow other people to view my email address', true),
		));
		echo $this->ZuluruForm->input('Person.0.alternate_email');
		echo $this->ZuluruForm->input('Person.0.publish_alternate_email', array(
			'label' => __('Allow other people to view my alternate email address', true),
		));
		if (Configure::read('feature.gravatar')) {
			if (Configure::read('feature.photos')) {
				$after = sprintf(__('You can have an image shown on your account by uploading a photo directly, or by enabling this setting and then create a <a href="http://www.gravatar.com">gravatar.com</a> account using the email address you\'ve associated with your %s account.', true), Configure::read('organization.short_name'));
			} else {
				$after = sprintf(__('You can have an image shown on your account if you enable this setting and then create a <a href="http://www.gravatar.com">gravatar.com</a> account using the email address you\'ve associated with your %s account.', true), Configure::read('organization.short_name'));
			}
			echo $this->ZuluruForm->input('Person.0.show_gravatar', array(
				'label' => __('Show Gravatar image for your account?', true),
				'after' => $this->Html->para (null, $after),
			));
		}
		if (Configure::read('profile.contact_for_feedback')) {
			echo $this->ZuluruForm->input('Person.0.contact_for_feedback', array(
				'label' => sprintf(__('From time to time, %s would like to contact members with information on our programs and to solicit feedback. Can %s contact you in this regard?', true), $short, $short),
				'checked' => true,
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
		if (Configure::read('profile.addr_street')) {
			echo $this->ZuluruForm->input('Person.0.addr_street', array(
				'label' => __('Street and Number', true),
				'after' => $this->Html->para (null, __('Number, street name, and apartment number if necessary.', true)),
			));
		}
		if (Configure::read('profile.addr_city')) {
			echo $this->ZuluruForm->input('Person.0.addr_city', array(
				'label' => __('City', true),
				'after' => $this->Html->para (null, __('Name of city.', true)),
			));
		}
		if (Configure::read('profile.addr_prov')) {
			echo $this->ZuluruForm->input('Person.0.addr_prov', array(
				'label' => __('Province', true),
				'type' => 'select',
				'empty' => '---',
				'options' => $provinces,
				'after' => $this->Html->para (null, __('Select a province/state from the list', true)),
			));
		}
		if (Configure::read('profile.addr_country')) {
			echo $this->ZuluruForm->input('Person.0.addr_country', array(
				'label' => __('Country', true),
				'type' => 'select',
				'empty' => '---',
				'options' => $countries,
				'hide_single' => true,
				'after' => $this->Html->para (null, __('Select a country from the list.', true)),
			));
		}
		if (Configure::read('profile.addr_postalcode')) {
			echo $this->ZuluruForm->input('Person.0.addr_postalcode', array(
				'label' => __('Postal Code', true),
				'after' => $this->Html->para (null, sprintf(__('Please enter a correct postal code matching the address above. %s uses this information to help locate new %s near its members.', true), $short, __(Configure::read('ui.fields'), true))),
			));
		}
	?>
	</fieldset>
	<?php endif; ?>
	<fieldset class="player" style="display:none;">
		<legend><?php __('Your Player Profile'); ?></legend>
	<?php
		echo $this->ZuluruForm->input('Person.0.gender', array(
			'type' => 'select',
			'empty' => '---',
			'options' => Configure::read('options.gender'),
		));
		if (Configure::read('profile.birthdate')) {
			if (Configure::read('feature.birth_year_only')) {
				echo $this->ZuluruForm->input('Person.0.birthdate', array(
					'dateFormat' => 'Y',
					'minYear' => Configure::read('options.year.born.min'),
					'maxYear' => Configure::read('options.year.born.max'),
					'empty' => '---',
					'after' => $this->Html->para(null, __('Please enter a correct birthdate; having accurate information is important for insurance purposes.', true)),
				));
				echo $this->Form->hidden('Person.0.birthdate.month', array('value' => 1));
				echo $this->Form->hidden('Person.0.birthdate.day', array('value' => 1));
			} else {
				echo $this->ZuluruForm->input('Person.0.birthdate', array(
					'minYear' => Configure::read('options.year.born.min'),
					'maxYear' => Configure::read('options.year.born.max'),
					'empty' => '---',
					'after' => $this->Html->para(null, __('Please enter a correct birthdate; having accurate information is important for insurance purposes.', true)),
				));
			}
		}
		if (Configure::read('profile.height')) {
			if (Configure::read('feature.units') == 'Metric') {
				$units = __('centimeters', true);
			} else {
				$units = __('inches (5 feet is 60 inches; 6 feet is 72 inches)', true);
			}
			echo $this->ZuluruForm->input('Person.0.height', array(
				'size' => 6,
				'after' => $this->Html->para(null, sprintf(__('Please enter your height in %s. This is used to help generate even teams for hat leagues.', true), $units)),
			));
		}

		if (in_array(Configure::read('profile.shirt_size'), array(PROFILE_USER_UPDATE, PROFILE_ADMIN_UPDATE))) {
			echo $this->ZuluruForm->input('Person.0.shirt_size', array(
				'type' => 'select',
				'empty' => '---',
				'options' => Configure::read('options.shirt_size'),
			));
		}
		if (Configure::read('feature.dog_questions')) {
			echo $this->ZuluruForm->input('Person.0.has_dog');
		}
		echo $this->element('people/skill_edit', array('prefix' => 'Person.0'));
	?>
	</fieldset>
	<?php if (Configure::read('profile.shirt_size')): ?>
	<fieldset class="coach" style="display:none;">
		<legend><?php __('Your Coaching Profile'); ?></legend>
	<?php
		if (Configure::read('profile.shirt_size')) {
			echo $this->ZuluruForm->input('Person.0.shirt_size', array(
				'type' => 'select',
				'empty' => '---',
				'options' => Configure::read('options.shirt_size'),
			));
		}
	?>
	</fieldset>
	<?php endif; ?>
	<fieldset class="parent" style="display:none;">
		<legend><?php __('Child Profile'); ?></legend>
	<?php
		echo $this->ZuluruForm->input('Person.1.first_name', array(
			'after' => $this->Html->para (null, __('First (and, if desired, middle) name.', true)),
		));
		echo $this->ZuluruForm->input('Person.1.last_name');
		echo $this->ZuluruForm->input('Person.1.gender', array(
			'type' => 'select',
			'empty' => '---',
			'options' => Configure::read('options.gender'),
		));
		if (Configure::read('profile.birthdate')) {
			if (Configure::read('feature.birth_year_only')) {
				echo $this->ZuluruForm->input('Person.1.birthdate', array(
					'dateFormat' => 'Y',
					'minYear' => Configure::read('options.year.born.min'),
					'maxYear' => Configure::read('options.year.born.max'),
					'empty' => '---',
					'after' => $this->Html->para(null, __('Please enter a correct birthdate; having accurate information is important for insurance purposes.', true)),
				));
				echo $this->Form->hidden('Person.1.birthdate.month', array('value' => 1));
				echo $this->Form->hidden('Person.1.birthdate.day', array('value' => 1));
			} else {
				echo $this->ZuluruForm->input('Person.1.birthdate', array(
					'minYear' => Configure::read('options.year.born.min'),
					'maxYear' => Configure::read('options.year.born.max'),
					'empty' => '---',
					'after' => $this->Html->para(null, __('Please enter a correct birthdate; having accurate information is important for insurance purposes.', true)),
				));
			}
		}
		if (Configure::read('profile.height')) {
			if (Configure::read('feature.units') == 'Metric') {
				$units = __('centimeters', true);
			} else {
				$units = __('inches (5 feet is 60 inches; 6 feet is 72 inches)', true);
			}
			echo $this->ZuluruForm->input('Person.1.height', array(
				'size' => 6,
				'after' => $this->Html->para(null, sprintf(__('Please enter your height in %s. This is used to help generate even teams for hat leagues.', true), $units)),
			));
		}
		if (in_array(Configure::read('profile.shirt_size'), array(PROFILE_USER_UPDATE, PROFILE_ADMIN_UPDATE))) {
			echo $this->ZuluruForm->input('Person.1.shirt_size', array(
				'type' => 'select',
				'empty' => '---',
				'options' => Configure::read('options.shirt_size'),
			));
		}
		echo $this->element('people/skill_edit', array('prefix' => 'Person.1'));
	?>
	</fieldset>
<?php
echo $this->Form->submit(__('Submit and save your information', true), array('div' => false, 'name' => 'create'));
echo $this->Form->submit(__('Save your information and add another child', true), array('div' => array('tag' => 'span', 'class' => 'parent', 'style' => 'display:none;'), 'name' => 'continue'));
echo $this->Form->end();
?>
</div>

<?php
if (Configure::read('profile.skill_level')) {
	$sports = Configure::read('options.sport');
	foreach (array_keys($sports) as $sport) {
		Configure::load("sport/$sport");
		if (Configure::read('sport.rating_questions')) {
			echo $this->element('people/rating', array('sport' => $sport));
		}
	}
}

// Handle changes to group checkboxes
$player = GROUP_PLAYER;
$parent = GROUP_PARENT;
$coach = GROUP_COACH;
$this->Js->get("#GroupGroup$player")->event('change', 'playerChanged();');
$this->Js->get("#GroupGroup$parent")->event('change', 'parentChanged();');
$this->Js->get("#GroupGroup$coach")->event('change', 'coachChanged();');
echo $this->Html->scriptBlock("
function playerChanged() {
	var checked = jQuery('#GroupGroup$player').prop('checked');
	if (checked) {
		jQuery('.player').css('display', '');
		jQuery('.player input, .player select').removeAttr('disabled');
	} else {
		jQuery('.player').css('display', 'none');
		jQuery('.player input, .player select').attr('disabled', 'disabled');
	}
}

function parentChanged() {
	var checked = jQuery('#GroupGroup$parent').prop('checked');
	if (checked) {
		jQuery('.parent').css('display', '');
		jQuery('.parent input, .parent select').removeAttr('disabled');
	} else {
		jQuery('.parent').css('display', 'none');
		jQuery('.parent input, .parent select').attr('disabled', 'disabled');
	}
}

function coachChanged() {
	var checked = jQuery('#GroupGroup$coach').prop('checked');
	if (checked) {
		jQuery('.coach').css('display', '');
		jQuery('.coach input, .coach select').removeAttr('disabled');
	} else {
		jQuery('.coach').css('display', 'none');
		jQuery('.coach input, .coach select').attr('disabled', 'disabled');
	}
}
");
$this->Js->buffer('playerChanged(); parentChanged(); coachChanged();');
?>
