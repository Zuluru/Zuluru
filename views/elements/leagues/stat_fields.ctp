<?php
// TODO: Include the list of positions, where applicable
$options = array();

$entered = Set::extract('/StatType[type=entered]', $stat_types);
$entered = Set::combine($entered, '{n}.StatType.id', '{n}.StatType.name');
if (!empty($entered)) {
	$options[__('Stats to enter', true)] = $entered;
}

$game_calc = Set::extract('/StatType[type=game_calc]', $stat_types);
$game_calc = Set::combine($game_calc, '{n}.StatType.id', '{n}.StatType.name');
if (!empty($game_calc)) {
	$options[__('Per-game calculated stats to display', true)] = $game_calc;
}

$season_total = Set::extract('/StatType[type=season_total]', $stat_types);
$season_total = Set::combine($season_total, '{n}.StatType.id', '{n}.StatType.name');
if (!empty($season_total)) {
	$options[__('Stats to display season totals of', true)] = $season_total;
}

$season_avg = Set::extract('/StatType[type=season_avg]', $stat_types);
$season_avg = Set::combine($season_avg, '{n}.StatType.id', '{n}.StatType.name');
if (!empty($season_avg)) {
	$options[__('Stats to display season averages of', true)] = $season_avg;
}

$season_calc = Set::extract('/StatType[type=season_calc]', $stat_types);
$season_calc = Set::combine($season_calc, '{n}.StatType.id', '{n}.StatType.name');
if (!empty($season_calc)) {
	$options[__('Stats to display season calculated values for', true)] = $season_calc;
}

echo $this->ZuluruForm->input('StatType', array(
	'label' => false,
	'multiple' => 'checkbox',
	'options' => $options,
));
?>
