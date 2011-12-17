<?php
$this->Html->addCrumb (__('Leagues', true));
if (isset ($add)) {
	$this->Html->addCrumb (__('Create', true));
} else {
	// TODO: simulate the long_name virtual field
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
			'after' => $this->Html->para (null, __('The full name of the league. Year and tier numbering will be automatically added.', true)),
		));
		echo $this->ZuluruForm->input('coord_list', array(
			'label' => __('Coordinator Email List', true),
			'size' => 70,
			'after' => $this->Html->para (null, __('An email alias for all coordinators of this league (can be a comma separated list of individual email addresses).', true)),
		));
		echo $this->ZuluruForm->input('capt_list', array(
			'label' => __('Captain Email List', true),
			'size' => 70,
			'after' => $this->Html->para (null, __('An email alias for all captains of this league.', true)),
		));
	?>
	</fieldset>
	<fieldset>
 		<legend><?php __('Dates'); ?></legend>
	<?php
		echo $this->ZuluruForm->input('season', array(
			'options' => Configure::read('options.season'),
			'empty' => '---',
			'after' => $this->Html->para (null, __('Season during which this league\'s games take place.', true)),
		));
		echo $this->ZuluruForm->input('open', array(
			'label' => 'First Game',
			'empty' => '---',
			'after' => $this->Html->para (null, __('Date of the first game in the schedule. Will be used to determine open/closed status.', true)),
		));
		echo $this->ZuluruForm->input('close', array(
			'label' => 'Last Game',
			'empty' => '---',
			'after' => $this->Html->para (null, __('Date of the last game in the schedule. Will be used to determine open/closed status.', true)),
		));
		echo $this->ZuluruForm->input('roster_deadline', array(
			'empty' => '---',
			'after' => $this->Html->para (null, __('The date after which teams are no longer allowed to edit their rosters. Leave blank for no deadline (changes can be made until the league is closed).', true)),
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
			'after' => $this->Html->para (null, __('Day, or days, on which this league will play.', true)),
		));
		echo $this->ZuluruForm->input('tier', array(
			'options' => Configure::read('options.tier'),
			'empty' => '---',
			'after' => $this->Html->para (null, __('Tier number. Choose 0 to not have numbered tiers.', true)),
		));
		echo $this->ZuluruForm->input('ratio', array(
			'label' => __('Gender Ratio', true),
			'options' => Configure::read('options.ratio'),
			'empty' => '---',
			'after' => $this->Html->para (null, __('Gender format for the league.', true)),
		));
		echo $this->Form->input('roster_rule', array(
			'cols' => 70,
			'after' => $this->Html->para (null, __('Rules that must be passed to allow a player to be added to the roster of a team in this league.', true) .
				' ' . $this->ZuluruHtml->help(array('action' => 'rules', 'rules'))),
		));
		echo $this->ZuluruForm->input('roster_method', array(
			'options' => Configure::read('options.roster_methods'),
			'empty' => '---',
			'after' => $this->Html->para (null, __('Do players need to accept invitations, or can they just be added? The latter has privacy policy implications and should be used only when necessary.', true)),
		));
	?>
	</fieldset>
	<fieldset>
 		<legend><?php __('Scheduling'); ?></legend>
	<?php
		echo $this->ZuluruForm->input('schedule_type', array(
			'options' => Configure::read('options.schedule_type'),
			'hide_single' => true,
			'empty' => '---',
			'default' => 'none',
			'after' => $this->Html->para (null, __('What type of scheduling to use. This affects how games are scheduled and standings displayed.', true)),
		));
	?>
		<div id="SchedulingFields">
		<?php
		echo $this->element('league/scheduling_fields', array('fields' => $league_obj->schedulingFields($is_admin, $is_coordinator)));
		$this->Js->get('#LeagueScheduleType')->event('change', $this->Js->request(
				array('action' => 'scheduling_fields'),
				array('update' => '#SchedulingFields', 'dataExpression' => true, 'data' => '$("#LeagueScheduleType").get()')
		));
		?>
		</div>
	<?php
		echo $this->ZuluruForm->input('exclude_teams', array(
			'options' => Configure::read('options.enable'),
			'empty' => '---',
			'after' => $this->Html->para (null, __('Allows coordinators to exclude teams from schedule generation.', true)),
		));
	?>
	</fieldset>
	<fieldset>
 		<legend><?php __('Scoring'); ?></legend>
	<?php
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
		echo $this->ZuluruForm->input('expected_max_score', array(
			'size' => 5,
			'default' => 17,
			'after' => $this->Html->para (null, __('Used as the size of the ratings table.', true)),
		));
		echo $this->ZuluruForm->input('email_after', array(
			'size' => 5,
			'after' => $this->Html->para (null, __('Email captains who haven\'t scored games after this many hours, no reminder if 0.', true)),
		));
		echo $this->ZuluruForm->input('finalize_after', array(
			'size' => 5,
			'after' => $this->Html->para (null, __('Games which haven\'t been scored will be automatically finalized after this many hours, no finalization if 0.', true)),
		));
		if (Configure::read('scoring.allstars')) {
			echo $this->ZuluruForm->input('allstars', array(
				'options' => Configure::read('options.allstar'),
				'empty' => '---',
				'after' => $this->Html->para (null, __('When to ask captains for allstar nominations.', true)),
			));
		}
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>

<?php echo $this->ZuluruHtml->script ('datepicker', array('inline' => false));
