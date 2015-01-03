<?php
$this->Html->addCrumb (__('Registration Events', true));
$this->Html->addCrumb (__('Wizard', true));
?>

<div class="events index">
<h2><?php __('Registration Wizard');?></h2>
<?php
echo $this->Html->para('highlight-message', sprintf (__('This wizard walks you through registration options based on your current status. As you register for things, different options may appear here. You might also want to review our %s.', true),
		$this->Html->link(__('complete list of offerings', true), array('action' => 'index'))));
?>

<?php if (empty($events)): ?>
<p class="warning-message">There are no events currently available for registration. Please check back periodically for updates.</p>
<?php else: ?>
<?php
echo $this->element('registrations/notice');

$events_by_type = array();
// TODO: Set::extract bug
$events = array_values ($events);
$event_types_available = array();
foreach ($types as $type) {
	$events_by_type[$type['EventType']['id']] = Set::extract ("/EventType[id={$type['EventType']['id']}]/..", $events);
	if (!empty($events_by_type[$type['EventType']['id']])) {
		$event_types_available[] = $type['EventType']['id'];
	}
}
if (empty($step) && count($event_types_available) == 1) {
	$step = array_pop($event_types_available);
}

switch ($step) {
	case 'membership':
	case 1:
		if (!empty ($events_by_type[1])) {
			echo $this->Html->tag('h3', __($events_by_type[1][0]['EventType']['name'], true));
			echo $this->Html->para(null, __('You are currently eligible for the following memberships.', true));
			echo $this->element('events/list', array('events' => $events_by_type[1]));
		} else {
			// TODO: Error message if people get here by accident
		}
		break;

	case 'league_team':
	case 2:
		if (!empty ($events_by_type[2])) {
			echo $this->Html->tag('h3', __($events_by_type[2][0]['EventType']['name'], true));
			echo $this->Html->para(null, __('You are currently eligible to register a team in the following leagues.', true));
			echo $this->element('events/list', array('events' => $events_by_type[2]));
		} else {
			// TODO: Error message if people get here by accident
		}
		break;

	case 'league_individual':
	case 3:
		if (!empty ($events_by_type[3])) {
			echo $this->Html->tag('h3', __($events_by_type[3][0]['EventType']['name'], true));
			echo $this->Html->para(null, __('You are currently eligible to register as an individual in the following leagues.', true));
			echo $this->element('events/list', array('events' => $events_by_type[3]));
		} else {
			// TODO: Error message if people get here by accident
		}
		break;

	case 'event_team':
	case 4:
		if (!empty ($events_by_type[4])) {
			echo $this->Html->tag('h3', __($events_by_type[4][0]['EventType']['name'], true));
			echo $this->Html->para(null, __('You are currently eligible to register a team for the following events.', true));
			echo $this->element('events/list', array('events' => $events_by_type[4]));
		} else {
			// TODO: Error message if people get here by accident
		}
		break;

	case 'event_individual':
	case 5:
		if (!empty ($events_by_type[5])) {
			echo $this->Html->tag('h3', __($events_by_type[5][0]['EventType']['name'], true));
			echo $this->Html->para(null, __('You are currently eligible to register as an individual for the following events.', true));
			echo $this->element('events/list', array('events' => $events_by_type[5]));
		} else {
			// TODO: Error message if people get here by accident
		}
		break;

	case 'clinic':
	case 6:
		if (!empty ($events_by_type[6])) {
			echo $this->Html->tag('h3', __($events_by_type[6][0]['EventType']['name'], true));
			echo $this->Html->para(null, __('You are currently eligible to register for the following clinics.', true));
			echo $this->element('events/list', array('events' => $events_by_type[6]));
		} else {
			// TODO: Error message if people get here by accident
		}
		break;

	case 'social_event':
	case 7:
		if (!empty ($events_by_type[7])) {
			echo $this->Html->tag('h3', __($events_by_type[7][0]['EventType']['name'], true));
			echo $this->Html->para(null, __('You can register for the following social events.', true));
			echo $this->element('events/list', array('events' => $events_by_type[7]));
		} else {
			// TODO: Error message if people get here by accident
		}
		break;

	default:
		if (!empty ($events_by_type[1])) {
			echo $this->Html->para(null, sprintf(__('You are eligible to %s. A membership is typically required before you can sign up for team-related events.', true), $this->Html->link(__('register for membership in the club', true), array('action' => 'wizard', 'membership'))));
			echo $this->Html->tag('span',
					$this->Html->link('Register for membership', array('action' => 'wizard', 'membership')),
					array('class' => 'actions'));
		}

		if (!empty ($events_by_type[2])) {
			echo $this->Html->para(null, sprintf(__('You are eligible to %s. This is for team coaches or captains looking to add their team for the upcoming season.', true), $this->Html->link(__('register a league team', true), array('action' => 'wizard', 'league_team'))));
			echo $this->Html->tag('span',
					$this->Html->link('Register a league team', array('action' => 'wizard', 'league_team')),
					array('class' => 'actions'));
		}

		if (!empty ($events_by_type[3])) {
			echo $this->Html->para(null, sprintf(__('You are eligible to %s. This is for individuals who do not already have a team and want to play on a "hat team".', true), $this->Html->link(__('register as an individual for league play', true), array('action' => 'wizard', 'league_individual'))));
			echo $this->Html->tag('span',
					$this->Html->link('Register as an individual', array('action' => 'wizard', 'league_individual')),
					array('class' => 'actions'));
		}

		if (!empty ($events_by_type[4])) {
			echo $this->Html->para(null, sprintf(__('You are eligible to %s. This is for team coaches or captains looking to add their team for a tournament or similar event.', true), $this->Html->link(__('register a team for a one-time event', true), array('action' => 'wizard', 'event_team'))));
			echo $this->Html->tag('span',
					$this->Html->link('Register a team for an event', array('action' => 'wizard', 'event_team')),
					array('class' => 'actions'));
		}

		if (!empty ($events_by_type[5])) {
			echo $this->Html->para(null, sprintf(__('You are eligible to %s. This is for individuals who do not already have a team and want to play on a "hat team" in a tournament or similar event.', true), $this->Html->link(__('register as an individual for a one-time event', true), array('action' => 'wizard', 'event_individual'))));
			echo $this->Html->tag('span',
					$this->Html->link('Register as an individual', array('action' => 'wizard', 'event_individual')),
					array('class' => 'actions'));
		}

		if (!empty ($events_by_type[6])) {
			echo $this->Html->para(null, sprintf(__('There are %s that you might be interested in.', true), $this->Html->link(__('upcoming clinics', true), array('action' => 'wizard', 'clinic'))));
			echo $this->Html->tag('span',
					$this->Html->link('Register for a clinic', array('action' => 'wizard', 'clinic')),
					array('class' => 'actions'));
		}

		if (!empty ($events_by_type[7])) {
			echo $this->Html->para(null, sprintf(__('There are %s that you might be interested in.', true), $this->Html->link(__('upcoming social events', true), array('action' => 'wizard', 'social_event'))));
			echo $this->Html->tag('span',
					$this->Html->link('Register for a social event', array('action' => 'wizard', 'social_event')),
					array('class' => 'actions'));
		}

		break;
}
?>
<?php endif; ?>
</div>
<?php echo $this->element('people/confirmation', array('fields' => array('skill_level', 'height', 'shirt_size', 'year_started'))); ?>