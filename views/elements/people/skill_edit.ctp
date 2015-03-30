<?php
// If neither year started nor skill level are enabled, we don't want any output from this.
if (!Configure::read('profile.year_started') && !Configure::read('profile.skill_level')) {
	return;
}

$sports = Configure::read('options.sport');
$admin = Configure::read('email.admin_email');
if (!isset($access)) {
	// New accounts can update all fields
	$access = array(1,2);
}
if (!isset($prefix)) {
	$prefix = $id_prefix = '';
} else {
	$id_prefix = str_replace('.', '', $prefix);
	$prefix .= '.';
}

$i = 0;
foreach ($sports as $sport => $name):
	Configure::load("sport/$sport");

	if (count($sports) > 1):
?>
	<fieldset>
		<legend><?php __($name); ?></legend>
<?php
		echo $this->ZuluruForm->input("{$prefix}Skill.{$i}.enabled", array(
			'type' => 'checkbox',
			'label' => sprintf(__('I will be playing %s', true), __($sport, true)),
		));

		$this->Js->get("#{$id_prefix}Skill{$i}Enabled")->event('change', "enableChanged($i, '$id_prefix');");
		$this->Js->buffer("enableChanged($i, '$id_prefix');");
	else:
		echo $this->ZuluruForm->hidden("{$prefix}Skill.{$i}.enabled", array(
			'value' => true,
		));
	endif;

	echo $this->ZuluruForm->hidden("{$prefix}Skill.{$i}.sport", array(
		'value' => $sport,
	));

	if (in_array (Configure::read('profile.year_started'), $access)) {
		echo $this->ZuluruForm->input("{$prefix}Skill.{$i}.year_started", array(
			'type' => 'select',
			'options' => $this->Form->__generateOptions('year', array(
					'min' => Configure::read('options.year.started.min'),
					'max' => Configure::read('options.year.started.max'),
					'order' => 'desc'
			)),
			'empty' => '---',
			'after' => $this->Html->para(null, __('The year you started playing in <strong>this</strong> league.', true)),
		));
	} else if (Configure::read('profile.year_started')) {
		echo $this->ZuluruForm->input("{$prefix}Skill.{$i}.year_started", array(
			'disabled' => 'true',
			'class' => 'disabled',
			'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your correct year started to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
		));
	}

	if (in_array (Configure::read('profile.skill_level'), $access)) {
		if (Configure::read('sport.rating_questions')) {
			$after = $this->Html->para(null, __('Please use the questionnaire to ', true) . $this->Html->link (__('calculate your rating', true), '#', array('onclick' => "dorating('$sport', '#{$id_prefix}Skill{$i}SkillLevel'); return false;")) . '.');
		} else {
			$after = null;
		}
		echo $this->ZuluruForm->input("{$prefix}Skill.{$i}.skill_level", array(
			'type' => 'select',
			'empty' => '---',
			'options' => Configure::read('options.skill'),
			'after' => $after,
		));
	} else if (Configure::read('profile.skill_level')) {
		echo $this->ZuluruForm->input("{$prefix}Skill.{$i}.skill_level", array(
			'disabled' => 'true',
			'class' => 'disabled',
			'size' => 70,
			'after' => $this->Html->para (null, __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your new skill level to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.', true),
		));
	}

	if (count($sports) > 1):
?>
	</fieldset>
<?php
	endif;
	++ $i;
endforeach;

if (count($sports) > 1 && !Configure::read('enable_changed_function_added')) {
	Configure::write('enable_changed_function_added', true);
	echo $this->Html->scriptBlock('
function enableChanged(i, prefix) {
	var checked = jQuery("#" + prefix + "Skill" + i + "Enabled").prop("checked");
	if (checked) {
		jQuery("#" + prefix + "Skill" + i + "YearStarted").closest("div").css("display", "");
		jQuery("#" + prefix + "Skill" + i + "SkillLevel").closest("div").css("display", "");
	} else {
		jQuery("#" + prefix + "Skill" + i + "YearStarted").closest("div").css("display", "none");
		jQuery("#" + prefix + "Skill" + i + "SkillLevel").closest("div").css("display", "none");
	}
}
	');
}
?>