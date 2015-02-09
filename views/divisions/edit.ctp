<?php
$this->Html->addCrumb (__('Divisions', true));
if (isset ($add)) {
	$this->Html->addCrumb (__('Create', true));
} else {
	$this->Html->addCrumb ($this->Form->value('League.name'));
	$division_name = $this->Form->value('Division.name');
	if (!empty($division_name)) {
		$this->Html->addCrumb ($division_name);
	}
	$this->Html->addCrumb (__('Edit', true));
}
?>

<div class="divisions form">
<?php echo $this->Form->create('Division', array('url' => Router::normalize($this->here)));?>
<p><?php echo $this->ZuluruHtml->icon('gears_32.png', array('class' => 'basic', 'style' => 'vertical-align:middle; padding-right: 5px;')); ?><a class="show_advanced basic" href="#">Show advanced configuration</a>
<?php echo $this->ZuluruHtml->icon('gear_32.png', array('class' => 'advanced', 'style' => 'vertical-align:middle; padding-right: 5px;')); ?><a class="show_basic advanced" href="#">Show basic configuration</a></p>
	<fieldset>
		<legend><?php __('Division Information'); ?></legend>
	<?php
		if (!isset ($add)) {
			echo $this->Form->input('id');
		} else {
			echo $this->Form->input('league_id', array(
					'empty' => true,
			));
		}
		echo $this->ZuluruForm->input('name', array(
			'size' => 70,
			'after' => $this->Html->para (null, __('The name of the division.', true)),
		));
		echo $this->ZuluruForm->input('coord_list', array(
			'div' => 'input advanced',
			'label' => __('Coordinator Email List', true),
			'size' => 70,
			'after' => $this->Html->para (null, __('An email alias for all coordinators of this division (can be a comma separated list of individual email addresses).', true)),
		));
		echo $this->ZuluruForm->input('capt_list', array(
			'div' => 'input advanced',
			'label' => __('Coach/Captain Email List', true),
			'size' => 70,
			'after' => $this->Html->para (null, __('An email alias for all coaches and captains of this division.', true)),
		));
		echo $this->ZuluruForm->input('header', array(
			'div' => 'input advanced',
			'cols' => 70,
			'rows' => 5,
			'after' => $this->Html->para (null, __('A short blurb to be displayed at the top of schedule and standings pages, HTML is allowed.', true)),
			'class' => 'mceAdvanced',
		));
		echo $this->ZuluruForm->input('footer', array(
			'div' => 'input advanced',
			'cols' => 70,
			'rows' => 5,
			'after' => $this->Html->para (null, __('A short blurb to be displayed at the bottom of schedule and standings pages, HTML is allowed.', true)),
			'class' => 'mceAdvanced',
		));
	?>
	</fieldset>
	<fieldset>
		<legend><?php __('Dates'); ?></legend>
	<?php
		echo $this->ZuluruForm->input('open', array(
			'label' => 'First Game',
			'empty' => '---',
			'minYear' => Configure::read('options.year.event.min'),
			'maxYear' => Configure::read('options.year.event.max'),
			'looseYears' => true,
			'after' => $this->Html->para (null, __('Date of the first game in the schedule. Will be used to determine open/closed status.', true)),
		));
		echo $this->ZuluruForm->input('close', array(
			'label' => 'Last Game',
			'empty' => '---',
			'minYear' => Configure::read('options.year.event.min'),
			'maxYear' => Configure::read('options.year.event.max'),
			'looseYears' => true,
			'after' => $this->Html->para (null, __('Date of the last game in the schedule. Will be used to determine open/closed status.', true)),
		));
		echo $this->ZuluruForm->input('roster_deadline', array(
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
		echo $this->ZuluruForm->input('ratio', array(
			'label' => __('Gender Ratio', true),
			'options' => Configure::read('sport.ratio'),
			'hide_single' => true,
			'empty' => '---',
			'after' => $this->Html->para (null, __('Gender format for the division.', true)),
		));
		echo $this->Form->input('roster_rule', array(
			'div' => 'input advanced',
			'cols' => 70,
			'after' => $this->Html->para (null, __('Rules that must be passed to allow a player to be added to the roster of a team in this division.', true) .
				' ' . $this->ZuluruHtml->help(array('action' => 'rules', 'rules'))),
		));
		echo $this->ZuluruForm->input('roster_method', array(
			'div' => 'input advanced',
			'options' => Configure::read('options.roster_methods'),
			'empty' => '---',
			'default' => 'invite',
			'after' => $this->Html->para (null, __('Do players need to accept invitations, or can they just be added? The latter has privacy policy implications and should be used only when necessary.', true)),
		));
		if (Configure::read('feature.registration')) {
			echo $this->ZuluruForm->input('flag_membership', array(
				'div' => 'input advanced',
				'options' => Configure::read('options.enable'),
				'empty' => '---',
				'default' => 0,
			));
		}
		echo $this->ZuluruForm->input('flag_roster_conflict', array(
			'div' => 'input advanced',
			'options' => Configure::read('options.enable'),
			'empty' => '---',
			'default' => true,
		));
		echo $this->ZuluruForm->input('flag_schedule_conflict', array(
			'div' => 'input advanced',
			'options' => Configure::read('options.enable'),
			'empty' => '---',
			'default' => true,
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
		if (isset($league_obj)) {
			echo $this->element('divisions/scheduling_fields', array('fields' => $league_obj->schedulingFields($is_admin, $is_coordinator)));
		}
		$this->Js->get('#DivisionScheduleType')->event('change', $this->Js->request(
				array('action' => 'scheduling_fields'),
				array('update' => '#SchedulingFields', 'dataExpression' => true, 'data' => 'jQuery("#DivisionScheduleType").get()')
		));
		?>
		</div>
	<?php
		echo $this->ZuluruForm->input('exclude_teams', array(
			'div' => 'input advanced',
			'options' => Configure::read('options.enable'),
			'empty' => '---',
			'default' => 0,
			'after' => $this->Html->para (null, __('Allows coordinators to exclude teams from schedule generation.', true)),
		));
		echo $this->ZuluruForm->input('double_booking', array(
			'div' => 'input advanced',
			'options' => Configure::read('options.enable'),
			'empty' => '---',
			'default' => 0,
			'after' => $this->Html->para (null, __('Allows coordinators to schedule multiple games in a single game slot.', true)),
		));
	?>
	</fieldset>
	<fieldset>
		<legend><?php __('Scoring'); ?></legend>
	<?php
		echo $this->ZuluruForm->input('rating_calculator', array(
			'options' => Configure::read('options.rating_calculator'),
			'hide_single' => true,
			'empty' => '---',
			'default' => 'none',
			'after' => $this->Html->para (null, __('What type of ratings calculation to use.', true)),
		));
		echo $this->ZuluruForm->input('email_after', array(
			'size' => 5,
			'default' => 0,
			'after' => $this->Html->para (null, __('Email coaches and captains who haven\'t scored games after this many hours, no reminder if 0.', true)),
		));
		echo $this->ZuluruForm->input('finalize_after', array(
			'size' => 5,
			'default' => 0,
			'after' => $this->Html->para (null, __('Games which haven\'t been scored will be automatically finalized after this many hours, no finalization if 0.', true)),
		));
		if (Configure::read('scoring.allstars')) {
			echo $this->ZuluruForm->input('allstars', array(
				'div' => 'input advanced',
				'options' => Configure::read('options.allstar'),
				'empty' => '---',
				'default' => 'never',
				'after' => $this->Html->para (null, __('When to ask coaches and captains for allstar nominations.', true)),
			));
			echo $this->ZuluruForm->input('Division.allstars_from', array(
				'div' => 'input advanced',
				'options' => Configure::read('options.allstar_from'),
				'empty' => '---',
				'default' => 'opponent',
				'after' => $this->Html->para (null, __('Which team will allstar nominations come from? Ignored if the above field is set to "never".', true)),
			));
		}
		if (Configure::read('scoring.most_spirited')) {
			echo $this->ZuluruForm->input('Division.most_spirited', array(
				'div' => 'input advanced',
				'options' => Configure::read('options.most_spirited'),
				'empty' => '---',
				'default' => 'never',
				'after' => $this->Html->para (null, __('When to ask coaches and captains for "most spirited player" nominations.', true)),
			));
		}
	?>
	</fieldset>
<p><?php echo $this->ZuluruHtml->icon('gears_32.png', array('class' => 'basic', 'style' => 'vertical-align:middle; padding-right: 5px;')); ?><a class="show_advanced basic" href="#">Show advanced configuration</a>
<?php echo $this->ZuluruHtml->icon('gear_32.png', array('class' => 'advanced', 'style' => 'vertical-align:middle; padding-right: 5px;')); ?><a class="show_basic advanced" href="#">Show basic configuration</a></p>
<?php echo $this->Form->end(__('Submit', true));?>
</div>

<?php
echo $this->ZuluruHtml->script ('datepicker.js', array('inline' => false));
$this->Js->get('.show_advanced')->event('click', 'jQuery(".advanced").show(); jQuery(".basic").hide();');
$this->Js->get('.show_basic')->event('click', 'jQuery(".advanced").hide(); jQuery(".basic").show();');
$this->Js->buffer('
jQuery(".advanced").hide();
');
if (Configure::read('feature.tiny_mce')) $this->TinyMce->editor('advanced');
?>