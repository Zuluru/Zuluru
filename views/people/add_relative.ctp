<?php
$this->Html->addCrumb (__('People', true));
$this->Html->addCrumb (__('Add Child', true));
?>

<div class="people add_relative form">
<h2><?php echo sprintf(__('Add %s', true), __('Child', true));?></h2>

<p>This process is intended for parents to add player profiles for their children.
This does not create a login account; the only access to the new profile will be through your account.</p>

	<fieldset class="player">
		<legend><?php __('Player Profile'); ?></legend>
<?php
echo $this->Form->create('Person', array('url' => Router::normalize($this->here)));

echo $this->ZuluruForm->input('first_name', array(
	'after' => $this->Html->para (null, __('First (and, if desired, middle) name.', true)),
));
echo $this->ZuluruForm->input('last_name');
echo $this->ZuluruForm->input('gender', array(
	'type' => 'select',
	'empty' => '---',
	'options' => Configure::read('options.gender'),
));
if (Configure::read('profile.birthdate')) {
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
if (Configure::read('profile.skill_level')) {
	if (Configure::read('sport.rating_questions')) {
		$after = $this->Html->para(null, __('Please use the questionnaire to ', true) . $this->Html->link (__('calculate your rating', true), '#', array('onclick' => 'dorating("#Person1SkillLevel"); return false;')) . '.');
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
if (Configure::read('profile.height')) {
	if (Configure::read('feature.units') == 'Metric') {
		$units = __('centimeters', true);
	} else {
		$units = __('inches (5 feet is 60 inches; 6 feet is 72 inches)', true);
	}
	echo $this->ZuluruForm->input('height', array(
		'size' => 6,
		'after' => $this->Html->para(null, sprintf(__('Please enter your height in %s. This is used to help generate even teams for hat leagues.', true), $units)),
	));
}
if (Configure::read('profile.shirt_size')) {
	echo $this->ZuluruForm->input('shirt_size', array(
		'type' => 'select',
		'empty' => '---',
		'options' => Configure::read('options.shirt_size'),
	));
}
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
<?php
echo $this->Form->submit(__('Submit and save', true), array('div' => false, 'name' => 'create'));
echo $this->Form->submit(__('Save and add another child', true), array('div' => false, 'name' => 'continue'));
echo $this->Form->end();
?>
</div>

<?php
// TODO: Handle more than one sport in a site
$sport = reset(array_keys(Configure::read('options.sport')));
if (Configure::read('profile.skill_level') && Configure::read('sport.rating_questions')) {
	echo $this->element('people/rating', array('sport' => $sport));
}
?>