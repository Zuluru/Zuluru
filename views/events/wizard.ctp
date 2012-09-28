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
foreach ($types as $type) {
	$events_by_type[$type['EventType']['id']] = Set::extract ("/EventType[id={$type['EventType']['id']}]/..", $events);
}

switch ($step) {
	case 'membership':
		if (!empty ($events_by_type[1])) {
			echo $this->Html->tag('h3', __($events_by_type[1][0]['EventType']['name'], true));
			echo $this->Html->para(null, __('You are currently eligible for the following memberships.', true));
			echo $this->element('events/list', array('events' => $events_by_type[1]));
		} else {
			// TODO: Error message if people get here by accident
		}
		break;

	case 'league_team':
		if (!empty ($events_by_type[2])) {
			echo $this->Html->tag('h3', __($events_by_type[2][0]['EventType']['name'], true));
			echo $this->Html->para(null, __('You are currently eligible to register a team in the following leagues.', true));
			echo $this->element('events/list', array('events' => $events_by_type[2]));
		} else {
			// TODO: Error message if people get here by accident
		}
		break;

	case 'league_individual':
		if (!empty ($events_by_type[3])) {
			echo $this->Html->tag('h3', __($events_by_type[3][0]['EventType']['name'], true));
			echo $this->Html->para(null, __('You are currently eligible to register as an individual in the following leagues.', true));
			echo $this->element('events/list', array('events' => $events_by_type[3]));
		} else {
			// TODO: Error message if people get here by accident
		}
		break;

	case 'event_team':
		if (!empty ($events_by_type[4])) {
			echo $this->Html->tag('h3', __($events_by_type[4][0]['EventType']['name'], true));
			echo $this->Html->para(null, __('You are currently eligible to register a team for the following events.', true));
			echo $this->element('events/list', array('events' => $events_by_type[4]));
		} else {
			// TODO: Error message if people get here by accident
		}
		break;

	case 'event_individual':
		if (!empty ($events_by_type[5])) {
			echo $this->Html->tag('h3', __($events_by_type[5][0]['EventType']['name'], true));
			echo $this->Html->para(null, __('You are currently eligible to register as an individual for the following events.', true));
			echo $this->element('events/list', array('events' => $events_by_type[5]));
		} else {
			// TODO: Error message if people get here by accident
		}
		break;

	case 'clinic':
		if (!empty ($events_by_type[6])) {
			echo $this->Html->tag('h3', __($events_by_type[6][0]['EventType']['name'], true));
			echo $this->Html->para(null, __('You are currently eligible to register for the following clinics.', true));
			echo $this->element('events/list', array('events' => $events_by_type[6]));
		} else {
			// TODO: Error message if people get here by accident
		}
		break;

	case 'social_event':
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
			echo $this->Html->para(null, __('You are eligible to register for membership in the club. A membership is typically required before you can sign up for team-related events.', true));
			echo $this->Html->tag('span',
					$this->Html->link('Register for membership', array('action' => 'wizard', 'membership')),
					array('class' => 'actions'));
		}

		if (!empty ($events_by_type[2])) {
			echo $this->Html->para(null, __('You are eligible to register a league team. This is for team captains looking to add their team for the upcoming season.', true));
			echo $this->Html->tag('span',
					$this->Html->link('Register a league team', array('action' => 'wizard', 'league_team')),
					array('class' => 'actions'));
		}

		if (!empty ($events_by_type[3])) {
			echo $this->Html->para(null, __('You are eligible to register as an individual for league play. This is for individuals who do not already have a team and want to play on a "hat team".', true));
			echo $this->Html->tag('span',
					$this->Html->link('Register as an individual', array('action' => 'wizard', 'league_individual')),
					array('class' => 'actions'));
		}

		if (!empty ($events_by_type[4])) {
			echo $this->Html->para(null, __('You are eligible to register a team for a one-time event. This is for team captains looking to add their team for a tournament or similar event.', true));
			echo $this->Html->tag('span',
					$this->Html->link('Register a team for an event', array('action' => 'wizard', 'event_team')),
					array('class' => 'actions'));
		}

		if (!empty ($events_by_type[5])) {
			echo $this->Html->para(null, __('You are eligible to register as an individual for a one-time event. This is for individuals who do not already have a team and want to play on a "hat team" in a tournament or similar event.', true));
			echo $this->Html->tag('span',
					$this->Html->link('Register as an individual', array('action' => 'wizard', 'event_individual')),
					array('class' => 'actions'));
		}

		if (!empty ($events_by_type[6])) {
			echo $this->Html->para(null, __('There are upcoming clinics that you might be interested in.', true));
			echo $this->Html->tag('span',
					$this->Html->link('Register for a clinic', array('action' => 'wizard', 'clinic')),
					array('class' => 'actions'));
		}

		if (!empty ($events_by_type[7])) {
			echo $this->Html->para(null, __('There are upcoming social events that you might be interested in.', true));
			echo $this->Html->tag('span',
					$this->Html->link('Register for a social event', array('action' => 'wizard', 'social_event')),
					array('class' => 'actions'));
		}

		break;
}
?>
<?php endif; ?>
</div>
