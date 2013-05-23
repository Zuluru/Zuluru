<?php
$this->Html->addCrumb (__('Leagues', true));
if (isset ($add)) {
	$this->Html->addCrumb (__('Create', true));
} else {
	$this->Html->addCrumb ($this->Form->value('League.name'));
	$this->Html->addCrumb (__('Edit', true));
}
$collapse = !empty($this->data['Division']['id']);
?>

<div class="leagues form">
<?php echo $this->Form->create('League', array('url' => Router::normalize($this->here)));?>
<p><a class="show_advanced basic" href="#">Show advanced configuration</a><a class="show_basic advanced" href="#">Show basic configuration</a></p>
	<fieldset>
 		<legend><?php __('League Information'); ?></legend>
	<?php
		if (!isset ($add)) {
			echo $this->Form->input('id');
			if ($collapse) {
				echo $this->Form->input('Division.id');
			}
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
		} else {
			echo $this->ZuluruForm->hidden('affiliate_id');
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

		if ($collapse) {
			echo $this->ZuluruForm->input('Division.coord_list', array(
				'div' => 'input advanced',
				'label' => __('Coordinator Email List', true),
				'size' => 70,
				'after' => $this->Html->para (null, __('An email alias for all coordinators of this division (can be a comma separated list of individual email addresses).', true)),
			));
			echo $this->ZuluruForm->input('Division.capt_list', array(
				'div' => 'input advanced',
				'label' => __('Captain Email List', true),
				'size' => 70,
				'after' => $this->Html->para (null, __('An email alias for all captains of this division.', true)),
			));
		}
	?>
	</fieldset>
	<?php if ($collapse): ?>
	<fieldset>
 		<legend><?php __('Dates'); ?></legend>
	<?php
		echo $this->ZuluruForm->input('Division.open', array(
			'label' => 'First Game',
			'empty' => '---',
			'minYear' => Configure::read('options.year.event.min'),
			'maxYear' => Configure::read('options.year.event.max'),
			'looseYears' => true,
			'after' => $this->Html->para (null, __('Date of the first game in the schedule. Will be used to determine open/closed status.', true)),
		));
		echo $this->ZuluruForm->input('Division.close', array(
			'label' => 'Last Game',
			'empty' => '---',
			'minYear' => Configure::read('options.year.event.min'),
			'maxYear' => Configure::read('options.year.event.max'),
			'looseYears' => true,
			'after' => $this->Html->para (null, __('Date of the last game in the schedule. Will be used to determine open/closed status.', true)),
		));
		echo $this->ZuluruForm->input('Division.roster_deadline', array(
			'empty' => '---',
			'minYear' => Configure::read('options.year.event.min'),
			'maxYear' => Configure::read('options.year.event.max'),
			'looseYears' => true,
			'after' => $this->Html->para (null, __('The date after which teams are no longer allowed to edit their rosters. Leave blank for no deadline (changes can be made until the division is closed).', true)),
		));
	?>
	</fieldset>
	<fieldset>
 		<legend><?php __('Specifics'); ?></legend>
	<?php
		echo $this->Form->input('Day', array(
			'label' => 'Day(s) of play',
			'type' => 'select',
			'multiple' => true,
			'size' => 8,
			'empty' => '---',
			'after' => $this->Html->para (null, __('Day, or days, on which this division will play.', true)),
		));
		echo $this->ZuluruForm->input('Division.ratio', array(
			'label' => __('Gender Ratio', true),
			'options' => Configure::read('sport.ratio'),
			'hide_single' => true,
			'empty' => '---',
			'after' => $this->Html->para (null, __('Gender format for the division.', true)),
		));
		echo $this->Form->input('Division.roster_rule', array(
			'div' => 'input advanced',
			'cols' => 70,
			'after' => $this->Html->para (null, __('Rules that must be passed to allow a player to be added to the roster of a team in this division.', true) .
				' ' . $this->ZuluruHtml->help(array('action' => 'rules', 'rules'))),
		));
		echo $this->ZuluruForm->input('Division.roster_method', array(
			'div' => 'input advanced',
			'options' => Configure::read('options.roster_methods'),
			'empty' => '---',
			'default' => 'invite',
			'after' => $this->Html->para (null, __('Do players need to accept invitations, or can they just be added? The latter has privacy policy implications and should be used only when necessary.', true)),
		));
		if (Configure::read('feature.registration')) {
			echo $this->ZuluruForm->input('Division.flag_membership', array(
				'div' => 'input advanced',
				'options' => Configure::read('options.enable'),
				'empty' => '---',
				'default' => 0,
			));
		}
		echo $this->ZuluruForm->input('Division.flag_roster_conflict', array(
			'div' => 'input advanced',
			'options' => Configure::read('options.enable'),
			'empty' => '---',
			'default' => true,
		));
		echo $this->ZuluruForm->input('Division.flag_schedule_conflict', array(
			'div' => 'input advanced',
			'options' => Configure::read('options.enable'),
			'empty' => '---',
			'default' => true,
		));
	?>
	</fieldset>
	<?php endif; ?>
	<fieldset<?php if (!$collapse) echo ' class="advanced"'; ?>>
 		<legend><?php __('Scheduling'); ?></legend>
	<?php
		if ($collapse) {
			echo $this->ZuluruForm->input('Division.schedule_type', array(
				'options' => Configure::read('options.schedule_type'),
				'hide_single' => true,
				'empty' => '---',
				'default' => 'none',
				'after' => $this->Html->para (null, __('What type of scheduling to use. This affects how games are scheduled and standings displayed.', true)),
			));
	?>
		<div id="SchedulingFields">
	<?php
			if (isset($league_obj)) {
				echo $this->element('divisions/scheduling_fields', array('fields' => $league_obj->schedulingFields($is_admin, $is_coordinator)));
			}
			$this->Js->get('#DivisionScheduleType')->event('change', $this->Js->request(
					array('controller' => 'divisions', 'action' => 'scheduling_fields'),
					array('update' => '#SchedulingFields', 'dataExpression' => true, 'data' => 'jQuery("#DivisionScheduleType").get()')
			));
	?>
		</div>
	<?php
			echo $this->ZuluruForm->input('Division.exclude_teams', array(
				'div' => 'input advanced',
				'options' => Configure::read('options.enable'),
				'empty' => '---',
				'default' => 0,
				'after' => $this->Html->para (null, __('Allows coordinators to exclude teams from schedule generation.', true)),
			));
		}

		echo $this->ZuluruForm->input('schedule_attempts', array(
			'div' => 'input advanced',
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
				'div' => 'input advanced',
				'options' => Configure::read('options.sotg_display'),
				'empty' => '---',
				'label' => 'Spirit Display',
				'default' => 'all',
				'after' => $this->Html->para (null, __('Control spirit display. "All" shows numeric scores and survey answers (if applicable) to any player. "Numeric" shows game scores but not survey answers. "Symbols Only" shows only star, check, and X, with no numeric values attached. "Coordinator Only" restricts viewing of any per-game information to coordinators only.', true)),
			));

			$tie_breaker_options = Configure::read('options.tie_breaker_spirit');
		} else {
			$tie_breaker_options = Configure::read('options.tie_breaker');
		}
		echo $this->ZuluruForm->input('tie_breaker', array(
			'div' => 'input advanced',
			'options' => $tie_breaker_options,
			'hide_single' => true,
			'empty' => '---',
			'default' => TIE_BREAKER_HTH_HTHPM_PM_GF_LOSS,
			'after' => $this->Html->para (null, __('Order of tie-breakers to use in standings.', true)),
		));

		if ($collapse) {
			echo $this->ZuluruForm->input('Division.rating_calculator', array(
				'options' => Configure::read('options.rating_calculator'),
				'hide_single' => true,
				'empty' => '---',
				'default' => 'none',
				'after' => $this->Html->para (null, __('What type of ratings calculation to use.', true)),
			));
			echo $this->ZuluruForm->input('Division.email_after', array(
				'size' => 5,
				'after' => $this->Html->para (null, __('Email captains who haven\'t scored games after this many hours, no reminder if 0.', true)),
			));
			echo $this->ZuluruForm->input('Division.finalize_after', array(
				'size' => 5,
				'after' => $this->Html->para (null, __('Games which haven\'t been scored will be automatically finalized after this many hours, no finalization if 0.', true)),
			));
			if (Configure::read('scoring.allstars')) {
				echo $this->ZuluruForm->input('Division.allstars', array(
					'div' => 'input advanced',
					'options' => Configure::read('options.allstar'),
					'empty' => '---',
					'default' => 'never',
					'after' => $this->Html->para (null, __('When to ask captains for allstar nominations.', true)),
				));
				echo $this->ZuluruForm->input('Division.allstars_from', array(
					'div' => 'input advanced',
					'options' => Configure::read('options.allstar_from'),
					'empty' => '---',
					'default' => 'opponent',
					'after' => $this->Html->para (null, __('Which team will allstar nominations come from? Ignored if the above field is set to "never".', true)),
				));
			}
		}

		echo $this->ZuluruForm->input('expected_max_score', array(
			'div' => 'input advanced',
			'size' => 5,
			'default' => 17,
			'after' => $this->Html->para (null, __('Used as the size of the ratings table.', true)),
		));
		if (Configure::read('scoring.stat_tracking')):
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
	?>
		</div>
	<?php endif; ?>
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

if ($collapse) {
	echo $this->ZuluruHtml->script ('datepicker', array('inline' => false));
}
$this->Js->get('.show_advanced')->event('click', 'jQuery(".advanced").show(); jQuery(".basic").hide();');
$this->Js->get('.show_basic')->event('click', 'jQuery(".advanced").hide(); jQuery(".basic").show();');
$this->Js->get('#LeagueStatTracking')->event('change', 'trackingCheckboxChanged();');
$this->Js->buffer('
jQuery(".advanced").hide();
trackingCheckboxChanged();
');
?>