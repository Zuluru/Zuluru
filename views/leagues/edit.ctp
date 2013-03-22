<?php
$this->Html->addCrumb (__('Leagues', true));
if (isset ($add)) {
	$this->Html->addCrumb (__('Create', true));
} else {
	$this->Html->addCrumb ($this->data['League']['name']);
	$this->Html->addCrumb (__('Edit', true));
}
?>

<div class="leagues form">
<?php echo $this->Form->create('League', array('url' => Router::normalize($this->here)));?>
	<fieldset>
 		<legend><?php __('League Information'); ?></legend>
	<?php
		if (!isset ($add)) {
			echo $this->Form->input('id');
		}
		echo $this->ZuluruForm->input('name', array(
			'size' => 70,
			'after' => $this->Html->para (null, __('The full name of the league. Year and season will be automatically added.', true)),
		));

		if (isset ($add)) {
			echo $this->ZuluruForm->input('affiliate_id', array(
				'options' => $affiliates,
				'hide_single' => true,
				'empty' => '---',
			));
		}

		$sports = Configure::read('options.sport');
		echo $this->ZuluruForm->input('sport', array(
			'options' => $sports,
			'hide_single' => true,
			'empty' => '---',
			'after' => $this->Html->para (null, __('Sport played in this league.', true)),
		));

		echo $this->ZuluruForm->input('season', array(
			'options' => Configure::read('options.season'),
			'hide_single' => true,
			'empty' => '---',
			'after' => $this->Html->para (null, __('Season during which this league\'s games take place.', true)),
		));
	?>
	</fieldset>
	<fieldset>
 		<legend><?php __('Scheduling'); ?></legend>
	<?php
		echo $this->ZuluruForm->input('schedule_attempts', array(
			'size' => 5,
			'default' => 100,
			'after' => $this->Html->para (null, __('Number of attempts to generate a schedule, before taking the best option.', true)),
		));
	?>
	</fieldset>
	<fieldset>
 		<legend><?php __('Scoring'); ?></legend>
	<?php
		if (Configure::read('feature.spirit')) {
			echo $this->Html->para('warning-message', __('NOTE: If you set the questionnaire to "' . Configure::read('options.spirit_questions.none') . '" and disable numeric entry, spirit will not be tracked for this league.', true));
			echo $this->ZuluruForm->input('sotg_questions', array(
				'options' => Configure::read('options.spirit_questions'),
				'empty' => '---',
				'label' => 'Spirit Questionnaire',
				'default' => Configure::read('scoring.spirit_questions'),
				'after' => $this->Html->para (null, __('Select which questionnaire to use for spirit scoring, or "' . Configure::read('options.spirit_questions.none') . '" to use numeric scoring only.', true)),
			));
			echo $this->ZuluruForm->input('numeric_sotg', array(
				'options' => Configure::read('options.enable'),
				'empty' => '---',
				'label' => 'Spirit Numeric Entry',
				'default' => Configure::read('scoring.spirit_numeric'),
				'after' => $this->Html->para (null, __('Enable or disable the entry of a numeric spirit score, independent of the questionnaire selected above.', true)),
			));
			echo $this->ZuluruForm->input('display_sotg', array(
				'options' => Configure::read('options.sotg_display'),
				'empty' => '---',
				'label' => 'Spirit Display',
				'after' => $this->Html->para (null, __('Control spirit display. "All" shows numeric scores and survey answers (if applicable) to any player. "Numeric" shows game scores but not survey answers. "Symbols Only" shows only star, check, and X, with no numeric values attached. "Coordinator Only" restricts viewing of any per-game information to coordinators only.', true)),
			));
		}
		echo $this->ZuluruForm->input('expected_max_score', array(
			'size' => 5,
			'default' => 17,
			'after' => $this->Html->para (null, __('Used as the size of the ratings table.', true)),
		));
		if (Configure::read('scoring.stat_tracking')) {
			echo $this->ZuluruForm->input('stat_tracking', array(
				'options' => Configure::read('options.stat_tracking'),
				'empty' => '---',
				'after' => $this->Html->para (null, __('When to ask captains for game stats.', true)),
			));
	?>
		<div id="StatDetails">
	<?php
			echo $this->Html->link('Select all stats', '#', array(
					'id' => "selectAll",
					'onclick' => "selectAll('StatDetails'); return false;",
			));

			$entered = Set::extract('/StatType[type=entered]', $stat_types);
			$entered = Set::combine($entered, '{n}.StatType.id', '{n}.StatType.name');

			$game_calc = Set::extract('/StatType[type=game_calc]', $stat_types);
			$game_calc = Set::combine($game_calc, '{n}.StatType.id', '{n}.StatType.name');

			$season_total = Set::extract('/StatType[type=season_total]', $stat_types);
			$season_total = Set::combine($season_total, '{n}.StatType.id', '{n}.StatType.name');

			$season_avg = Set::extract('/StatType[type=season_avg]', $stat_types);
			$season_avg = Set::combine($season_avg, '{n}.StatType.id', '{n}.StatType.name');

			$season_calc = Set::extract('/StatType[type=season_calc]', $stat_types);
			$season_calc = Set::combine($season_calc, '{n}.StatType.id', '{n}.StatType.name');

			echo $this->ZuluruForm->input('StatType', array(
				'label' => false,
				'multiple' => 'checkbox',
				'options' => array(
					'Stats to enter' => $entered,
					'Per-game calculated stats to display' => $game_calc,
					'Stats to display season totals of' => $season_total,
					'Stats to display season averages of' => $season_avg,
					'Stats to display season calculated values for' => $season_calc,
				),
			));
		}
	?>
		</div>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>

<?php
// Add JavaScript functions for "select all" buttons and hiding blocks of fields

echo $this->Html->scriptBlock("
function trackingCheckboxChanged() {
	setting = jQuery('#LeagueStatTracking').val();
	if (setting == '' || setting == 'never') {
		jQuery('#StatDetails').css('display', 'none');
	} else {
		jQuery('#StatDetails').css('display', '');
	}
}

function selectAll(id) {
	var label = jQuery('#selectAll').text();
	var check = true;
	if (label.substr(0,6) == 'Select') {
		jQuery('#selectAll').text('Unselect all stats');
	} else {
		jQuery('#selectAll').text('Select all stats');
		check = false;
	}

	jQuery('#' + id + ' :checkbox').each(function () {
		jQuery(this).attr('checked', check);
	});
}
");

$this->Js->buffer('
jQuery("#LeagueStatTracking").change(function(){trackingCheckboxChanged();});
trackingCheckboxChanged();
');
?>