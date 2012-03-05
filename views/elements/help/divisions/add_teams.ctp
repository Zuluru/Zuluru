<p>If you are coordinating a division which will have one or more "hat" teams (assembled from players who have siged up individually), you may want to make use of the "add teams" option.
This will create up to eight teams at a time, with common settings.
You provide the names of the teams that you want to create, leaving extra name fields blank (you must remove the default values) if you are creating less than eight.
You must also indicate whether rosters will be open or closed, as well as some attendance tracking details (see below).</p>
<p>When you click "submit", the requested teams will be created with the specified settings.
Rosters will be blank, and shirt colours will be assigned based on a pre-set rotation of common colours (<?php echo implode(', ', Configure::read('automatic_team_colours')); ?>).
<?php if ($is_admin): ?>You can change this list of colours by adjusting the "automatic_team_colours" setting through config/features_custom.php<?php endif; ?></p>
<?php
echo $this->element('help/topics', array(
		'section' => 'teams/edit',
		'topics' => array(
			'open_roster',
			'track_attendance',
		),
		'compact' => true,
));
?>