<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb ($person['Person']['full_name']);
$this->Html->addCrumb (__('View Waiver', true));
$this->Html->addCrumb ($waiver['Waiver']['name']);
?>

<?php
if (!empty($person['Waiver'])) {
	if (isset($date)) {
		$message = __('You accepted this waiver at %s on %s', true);
	} else {
		$message = __('You most recently accepted this waiver at %s on %s', true);
	}
	if ($person['Waiver'][0]['expiry_type'] != 'never') {
		$message .= ', ' . __('covering the dates %s to %s', true);

	}
	$message .= '.';

	$message = sprintf($message,
			$this->ZuluruTime->time($person['Waiver'][0]['WaiversPerson']['created']), $this->ZuluruTime->fulldate($person['Waiver'][0]['WaiversPerson']['created']),
			$this->ZuluruTime->fulldate($person['Waiver'][0]['WaiversPerson']['valid_from']), $this->ZuluruTime->fulldate($person['Waiver'][0]['WaiversPerson']['valid_until']));
} else {
	$url = array('action' => 'sign', 'waiver' => $waiver['Waiver']['id']);
	if (isset($date)) {
		$message = sprintf(__('You have not accepted this waiver for the dates %s to %s.', true),
				$this->ZuluruTime->fulldate($valid_from), $this->ZuluruTime->fulldate($valid_until));
		$url['date'] = $date;
	} else {
		$message = __('You haven\'t accepted this waiver.', true);
		$url['date'] = date('Y-m-d');
	}
	if ($waiver['Waiver']['active']) {
		$message .= ' ' . sprintf(__('You may %s; if you choose not to, you may be prompted to do so at a later time.', true),
				$this->Html->link(__('accept it now', true), $url));
	}
}
echo $this->Html->para ('highlight-message', $message);

$variables = array(
	'%name%' => Configure::read('organization.name'),
	'%short_name%' => Configure::read('organization.short_name'),
	'%field%' => __(Configure::read('ui.field'), true),
	'%fields%' => __(Configure::read('ui.fields'), true),
	'%Field%' => __(Configure::read('ui.field_cap'), true),
	'%Fields%' => __(Configure::read('ui.fields_cap'), true),
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
?>
