<?php
$this->Html->addCrumb (__('Settings', true));
$this->Html->addCrumb (__('Scoring', true));
?>

<div class="settings form">
<?php
if ($affiliate) {
	$defaults = array('empty' => __('Use default', true));
} else {
	$defaults = array('empty' => false);
}
echo $this->ZuluruForm->create('Settings', array(
		'url' => Router::normalize($this->here),
        'inputDefaults' => $defaults,
));

echo $this->element('settings/banner');
?>
	<fieldset>
		<legend><?php __('Defaulted Games'); ?></legend>
	<?php
	echo $this->element('settings/input', array(
		'category' => 'scoring',
		'name' => 'default_winning_score',
		'options' => array(
			'label' => __('Winning score to record for defaulted games', true),
			'size' => 6,
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'scoring',
		'name' => 'default_losing_score',
		'options' => array(
			'label' => __('Losing score to record for defaulted games', true),
			'size' => 6,
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'scoring',
		'name' => 'default_transfer_ratings',
		'options' => array(
			'label' => __('Transfer ratings points for defaulted games', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
		),
	));
	?>
	</fieldset>

	<?php if (Configure::read('feature.spirit')): ?>
	<fieldset>
		<legend><?php __('Spirit Scores'); ?></legend>
	<?php
	echo $this->element('settings/input', array(
		'category' => 'scoring',
		'name' => 'spirit_questions',
		'options' => array(
			'label' => __('Spirit questions', true),
			'type' => 'select',
			'options' => Configure::read ('options.spirit_questions'),
			'after' => __('Default type of spirit questions to use when creating a new league.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'scoring',
		'name' => 'spirit_numeric',
		'options' => array(
			'label' => __('Spirit numeric', true),
			'type' => 'radio',
			'options' => Configure::read ('options.enable'),
			'after' => __('Default enable or disable entry of numeric spirit scores when creating a new league.', true),
		),
	));

	echo $this->Html->para(null, __('By using various combinations of questions and numeric entry above, you can have just the questionnaire, just the numeric entry, both or neither.', true));
	echo $this->Html->para(null, __('The values set above will be the default value for leagues, but can be overridden on a per-league basis.', true));

	echo $this->element('settings/input', array(
		'category' => 'scoring',
		'name' => 'spirit_max',
		'options' => array(
			'label' => __('Maximum spirit score, when no questionnaire is used', true),
			'size' => 6,
		),
	));

	echo $this->element('settings/input', array(
		'category' => 'scoring',
		'name' => 'missing_score_spirit_penalty',
		'options' => array(
			'label' => __('Spirit penalty for not entering score', true),
			'size' => 6,
		),
	));

	echo $this->element('settings/input', array(
		'category' => 'scoring',
		'name' => 'spirit_default',
		'options' => array(
			'label' => __('Spirit default', true),
			'type' => 'radio',
			'options' => Configure::read ('options.enable'),
			'after' => __('Include a default spirit score when not entered.', true),
		),
	));
	?>
	</fieldset>
	<?php endif; ?>

	<fieldset>
		<legend><?php __('Score Entry Features'); ?></legend>
	<?php
	echo $this->element('settings/input', array(
		'category' => 'scoring',
		'name' => 'allstars',
		'options' => array(
			'label' => __('Allstars', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('If enabled, all-star submissions will be a per-league option; otherwise, they will be disabled entirely.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'scoring',
		'name' => 'incident_reports',
		'options' => array(
			'label' => __('Incident reports', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('If enabled, captains will be allowed to file incident reports when submitting scores.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'scoring',
		'name' => 'most_spirited',
		'options' => array(
			'label' => __('Most spirited', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('If enabled, "most spirited player" submissions will be a per-league option; otherwise, they will be disabled entirely.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'scoring',
		'name' => 'stat_tracking',
		'options' => array(
			'label' => __('Handle stat submission and tracking as part of game scoring', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('Enable or disable stat tracking options. If enabled here, stats can still be disabled on a per-league basis.', true),
		),
	));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
