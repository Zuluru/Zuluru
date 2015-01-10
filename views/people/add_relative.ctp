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
echo $this->element('people/skill_edit');
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
if (Configure::read('profile.skill_level')) {
	$sports = Configure::read('options.sport');
	foreach (array_keys($sports) as $sport) {
		Configure::load("sport/$sport");
		if (Configure::read('sport.rating_questions')) {
			echo $this->element('people/rating', array('sport' => $sport));
		}
	}
}
?>