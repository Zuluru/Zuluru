<?php
$this->Html->addCrumb (__('Users', true));
$this->Html->addCrumb (__('Create', true));

$short = Configure::read('organization.short_name');

$access = array(1);

// TODO: Handle more than one sport in a site
$sport = array_shift(array_keys(Configure::read('options.sport')));
?>

<p>To create a new account, fill in all the fields below and click 'Submit' when done. Your account will be placed on hold until approved by an administrator. Once approved, you will be allocated a membership number, and have full access to the system.</p>
<p><strong>NOTE:</strong> If you already have an account from a previous season, <strong>DO NOT CREATE ANOTHER ONE</strong>! Instead, please <a href="<?php echo Configure::read('urls.password_reset'); ?>">follow these instructions</a> to regain access to your account.</p>
<p>Note that email and phone publish settings below only apply to regular players. Captains will always have access to view the phone numbers and email addresses of their confirmed players. All Team Captains will also have their email address viewable by other players.</p>
<p>If you have concerns about the data <?php echo $short; ?> collects, please see our <strong><a href="<?php echo Configure::read('urls.privacy_policy'); ?>" target="_new">Privacy Policy</a>.</strong></p>

<div class="users form">
<?php echo $this->Form->create('User', array('url' => Router::normalize($this->here)));?>
	<fieldset>
 		<legend><?php __('Identity'); ?></legend>
	<?php
		echo $this->ZuluruForm->input('first_name', array(
			'after' => $this->Html->para (null, __('First (and, if desired, middle) name.', true)),
		));
		echo $this->ZuluruForm->input('last_name');
		echo $this->ZuluruForm->input('user_name');
		echo $this->ZuluruForm->input('gender', array(
			'type' => 'select',
			'empty' => '---',
			'options' => Configure::read('options.gender'),
		));
	?>
	</fieldset>
	<fieldset>
 		<legend><?php __('Password'); ?></legend>
	<?php
		echo $this->ZuluruForm->input('passwd', array('type' => 'password', 'label' => 'Password'));
		echo $this->ZuluruForm->input('confirm_passwd', array('type' => 'password', 'label' => 'Confirm Password'));
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
		echo $this->ZuluruForm->input('email');
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
		if (Configure::read('profile.addr_street')) {
			echo $this->ZuluruForm->input('addr_street', array(
				'label' => __('Street and Number', true),
				'after' => $this->Html->para (null, __('Number, street name, and apartment number if necessary.', true)),
			));
		}
		if (Configure::read('profile.addr_city')) {
			echo $this->ZuluruForm->input('addr_city', array(
				'label' => __('City', true),
				'after' => $this->Html->para (null, __('Name of city.', true)),
			));
		}
		if (Configure::read('profile.addr_prov')) {
			echo $this->ZuluruForm->input('addr_prov', array(
				'label' => __('Province', true),
				'type' => 'select',
				'empty' => '---',
				'options' => $provinces,
				'after' => $this->Html->para (null, __('Select a province/state from the list', true)),
			));
		}
		if (Configure::read('profile.addr_country')) {
			echo $this->ZuluruForm->input('addr_country', array(
				'label' => __('Country', true),
				'type' => 'select',
				'empty' => '---',
				'options' => $countries,
				'after' => $this->Html->para (null, __('Select a country from the list.', true)),
			));
		}
		if (Configure::read('profile.addr_postalcode')) {
			$fields = __(Configure::read('ui.fields'), true);
			echo $this->ZuluruForm->input('addr_postalcode', array(
				'label' => __('Postal Code', true),
				'after' => $this->Html->para (null, sprintf(__("Please enter a correct postal code matching the address above. $short uses this information to help locate new %s near its members.", true), $fields)),
			));
		}
	?>
	</fieldset>
	<?php endif; ?>
	<?php if (Configure::read('profile.home_phone') || Configure::read('profile.work_phone') ||
				Configure::read('profile.mobile_phone')): ?>
	<fieldset>
 		<legend><?php __('Telephone Numbers'); ?></legend>
	<?php
		if (Configure::read('profile.home_phone')) {
			echo $this->ZuluruForm->input('home_phone', array(
				'after' => $this->Html->para (null, __('Enter your home telephone number. If you have only a mobile phone, enter that number both here and below.', true)),
			));
			echo $this->ZuluruForm->input('publish_home_phone', array(
				'label' => __('Allow other players to view home number', true),
			));
		}
		if (Configure::read('profile.work_phone')) {
			echo $this->ZuluruForm->input('work_phone', array(
				'after' => $this->Html->para (null, __('Enter your work telephone number (optional).', true)),
			));
			echo $this->ZuluruForm->input('work_ext', array(
				'label' => 'Work Extension',
				'after' => $this->Html->para (null, __('Enter your work extension (optional).', true)),
			));
			echo $this->ZuluruForm->input('publish_work_phone', array(
				'label' => __('Allow other players to view work number', true),
			));
		}
		if (Configure::read('profile.mobile_phone')) {
			echo $this->ZuluruForm->input('mobile_phone', array(
				'after' => $this->Html->para (null, __('Enter your cell or pager number (optional).', true)),
			));
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
 		<legend><?php __('Player and Skill Information'); ?></legend>
	<?php
		if (Configure::read('profile.skill_level')) {
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
		}
		if (Configure::read('profile.year_started')) {
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
		}
		if (Configure::read('profile.birthdate')) {
			echo $this->ZuluruForm->input('birthdate', array(
				'minYear' => Configure::read('options.year.born.min'),
				'maxYear' => Configure::read('options.year.born.max'),
				'after' => $this->Html->para(null, __('Please enter a correct birthdate; having accurate information is important for insurance purposes.', true)),
			));
		}
		if (Configure::read('profile.height')) {
			echo $this->ZuluruForm->input('height', array(
				'size' => 6,
				'after' => $this->Html->para(null, __('Please enter your height in inches (5 feet is 60 inches; 6 feet is 72 inches). This is used to help generate even teams for hat leagues.', true)),
			));
		}
		if (Configure::read('profile.shirt_size')) {
			echo $this->ZuluruForm->input('shirt_size', array(
				'type' => 'select',
				'empty' => '---',
				'options' => Configure::read('options.shirt_size'),
			));
		}
		if (Configure::read('feature.dog_questions')) {
			echo $this->ZuluruForm->input('has_dog');
		}
		if (Configure::read('profile.willing_to_volunteer')) {
			echo $this->ZuluruForm->input('willing_to_volunteer', array(
				'label' => __("Can $short contact you about volunteering?", true),
			));
		}
		if (Configure::read('profile.contact_for_feedback')) {
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
if (Configure::read('sport.rating_questions')) {
	echo $this->element('people/rating', array('sport' => $sport, 'field' => '#UserSkillLevel'));
}
?>
