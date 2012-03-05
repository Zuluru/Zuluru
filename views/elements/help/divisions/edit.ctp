<p>The "edit division" page is used to update details of your division.
Only coordinators have permission to edit division details.</p>
<p>Divisions are initially set up and configured by a system administrator, so coordinators should only make changes to these settings in extreme circumstances, and they should inform the administrator of any such changes.
However, it's useful to understand the meanings of the various settings for your division.</p>
<?php if ($is_admin): ?>
<p>The "create division" page is essentially identical to this page.</p>
<?php endif; ?>
<?php
echo $this->element('help/topics', array(
		'section' => 'divisions/edit',
		'topics' => array(
			'name',
			'schedule_type',
			'current_round',
			'games_before_repeat',
			'exclude_teams',
			'rating_calculator',
		),
		'compact' => true,
));
?>
