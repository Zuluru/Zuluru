<?php
$this->Html->addCrumb (__('Players', true));
// TODO: simulate the name virtual field
$this->Html->addCrumb ("{$this->data['Person']['first_name']} {$this->data['Person']['last_name']}");
$this->Html->addCrumb (__('Edit', true));

$short = Configure::read('organization.short_name');
?>

<p>Note that email and phone publish settings below only apply to regular players. Captains will always have access to view the phone numbers and email addresses of their confirmed players. All Team Captains will also have their email address viewable by other players.</p>
<p>If you have concerns about the data <?php echo $short; ?> collects, please see our <strong><a href="<?php echo Configure::read('urls.privacy_policy'); ?>" target="_new">Privacy Policy</a>.</strong></p>

<div class="people form">
<?php echo $this->Form->create('Person', array('url' => Router::normalize($this->here)));?>
	<fieldset>
 		<legend><?php __('Identity'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->ZuluruForm->input('first_name', array(
			'after' => $this->Html->para (null, __('First (and, if desired, middle) name.', true)),
		));
		echo $this->ZuluruForm->input('last_name');
		if (!Configure::read('feature.manage_accounts')) {
			$username_edit = sprintf (Configure::read('urls.username_edit'), $id);
			if ($username_edit) {
				$username_link = $this->Html->link (Configure::read('feature.manage_name') . ' ' . __('username', true), $username_edit);
				echo $this->ZuluruForm->input('email', array(
					'disabled' => 'true',
					'after' => $this->Html->para (null, __('To change this, edit your', true) . ' ' . $username_link),
					));
			} else {
				$admin = Configure::read('email.admin_email');
				echo $this->ZuluruForm->input('user_name', array(
					'disabled' => 'true',
					'after' => $this->Html->para (null, __('To change this, please email your existing user name and preferred new user name to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.'),
				));
			}
		} else {
			echo $this->ZuluruForm->input('user_name');
		}
		echo $this->ZuluruForm->input('gender', array(
			'type' => 'select',
			'empty' => '---',
			'options' => Configure::read('options.gender'),
		));
	?>
	</fieldset>
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
	<fieldset>
 		<legend><?php __('Street Address'); ?></legend>
	<?php
		echo $this->ZuluruForm->input('addr_street', array(
			'label' => __('Street and Number', true),
			'after' => $this->Html->para (null, __('Number, street name, and apartment number if necessary.', true)),
		));
		echo $this->ZuluruForm->input('addr_city', array(
			'label' => __('City', true),
			'after' => $this->Html->para (null, __('Name of city.', true)),
		));
		echo $this->ZuluruForm->input('addr_prov', array(
			'label' => __('Province', true),
			'type' => 'select',
			'empty' => '---',
			'options' => $provinces,
			'after' => $this->Html->para (null, __('Select a province/state from the list', true)),
		));
		echo $this->ZuluruForm->input('addr_country', array(
			'label' => __('Country', true),
			'type' => 'select',
			'empty' => '---',
			'options' => $countries,
			'after' => $this->Html->para (null, __('Select a country from the list.', true)),
		));
		echo $this->ZuluruForm->input('addr_postalcode', array(
			'label' => __('Postal Code', true),
			'after' => $this->Html->para (null, __("Please enter a correct postal code matching the address above. $short uses this information to help locate new fields near its members.", true)),
		));
	?>
	</fieldset>
	<fieldset>
 		<legend><?php __('Telephone Numbers'); ?></legend>
	<?php
		echo $this->ZuluruForm->input('home_phone', array(
			'after' => $this->Html->para (null, __('Enter your home telephone number. If you have only a mobile phone, enter that number both here and below.', true)),
		));
		echo $this->ZuluruForm->input('publish_home_phone', array(
			'label' => __('Allow other players to view home number', true),
		));
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
		echo $this->ZuluruForm->input('mobile_phone', array(
			'after' => $this->Html->para (null, __('Enter your cell or pager number (optional).', true)),
		));
		echo $this->ZuluruForm->input('publish_mobile_phone', array(
			'label' => __('Allow other players to view mobile number', true),
		));
	?>
	</fieldset>
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
	<fieldset>
 		<legend><?php __('Player and Skill Information'); ?></legend>
	<?php
		echo $this->ZuluruForm->input('skill_level', array(
			'type' => 'select',
			'empty' => '---',
			'options' => Configure::read('options.skill'),
			'after' => $this->Html->para(null, __('Please use the questionnaire to ', true) . $this->Html->link (__('calculate your rating', true), '#', array('onclick' => 'dorating(); return false;')) . '.'),
		));
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
		echo $this->ZuluruForm->input('birthdate', array(
			'minYear' => Configure::read('options.year.born.min'),
			'maxYear' => Configure::read('options.year.born.max'),
			'after' => $this->Html->para(null, __('Please enter a correct birthdate; having accurate information is important for insurance purposes.', true)),
		));
		echo $this->ZuluruForm->input('height', array(
			'size' => 6,
			'after' => $this->Html->para(null, __('Please enter your height in inches (5 feet is 60 inches; 6 feet is 72 inches). This is used to help generate even teams for hat leagues.', true)),
		));
		echo $this->ZuluruForm->input('shirt_size', array(
			'type' => 'select',
			'empty' => '---',
			'options' => Configure::read('options.shirt_size'),
		));
		if (Configure::read('feature.dog_questions')) {
			echo $this->ZuluruForm->input('has_dog');
		}
		echo $this->ZuluruForm->input('willing_to_volunteer', array(
			'label' => __("Can $short contact you about volunteering?", true),
		));
		echo $this->ZuluruForm->input('contact_for_feedback', array(
			'label' => __("From time to time, $short would like to contact members with information on our programs and to solicit feedback. Can $short contact you in this regard?"),
		));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>

<?php
// TODO: Handle more than one sport in a site
$sport = array_shift(array_keys(Configure::read('options.sport')));
echo $this->element('people/rating', array('sport' => $sport, 'field' => '#PersonSkillLevel'));
?>
