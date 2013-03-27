<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb ($person['Person']['full_name']);
$this->Html->addCrumb (__('Sign Waiver', true));
$this->Html->addCrumb ($waiver['Waiver']['name']);
?>

<?php
$variables = array(
	'%name%' => Configure::read('organization.name'),
	'%short_name%' => Configure::read('organization.short_name'),
	'%field%' => Configure::read('ui.field'),
	'%fields%' => Configure::read('ui.fields'),
	'%Field%' => Configure::read('ui.field_cap'),
	'%Fields%' => Configure::read('ui.fields_cap'),
	'%valid_from%' => date('F j, Y', strtotime($valid_from)),
	'%valid_from_year%' => date('Y', strtotime($valid_from)),
	'%valid_until%' => date('F j, Y', strtotime($valid_until)),
	'%valid_until_year%' => date('Y', strtotime($valid_until)),
);
if ($variables['%valid_from_year%'] == $variables['%valid_until_year%']) {
	$variables['%valid_years%'] = $variables['%valid_from_year%'];
} else {
	$variables['%valid_years%'] = "{$variables['%valid_from_year%']}-{$variables['%valid_until_year%']}";
}
echo strtr($waiver['Waiver']['text'], $variables);

echo $this->Form->create('Person', array('url' => Router::normalize($this->here)));
echo $this->Html->para(null,
	$this->Form->input('signed', array(
			'options' => array(
				'yes' => 'I agree to the above conditions',
				'no' => 'I DO NOT agree to the above conditions',
			),
			'type' => 'radio',
			'legend' => false,
	))
);
echo $this->Form->end(__('Submit', true));
?>
