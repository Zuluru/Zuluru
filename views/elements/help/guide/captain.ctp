<h2>Captain Guide</h2>
<p>So, you want to be a captain? <?php echo ZULURU; ?> includes many tools and features to make this often thankless job much easier.</p>

<?php
	echo $this->element('help/topics', array(
			'section' => 'teams',
			'topics' => array(
				'edit' => array(
					'title' => 'Team Creation and Editing',
					'image' => 'edit_32.png',
				),
			),
	));

	echo $this->element('help/topics', array(
			'section' => 'teams',
			'topics' => array(
				'roster_add' => array(
					'title' => 'Adding Players',
					'image' => 'roster_add_32.png',
				),
				'roster_role' => 'Promoting/Demoting/Removing Players',
			),
	));

	echo $this->element('help/topics', array(
			'section' => 'games',
			'topics' => array(
				'recent_and_upcoming' => 'Recent and Upcoming Games',
			),
	));
?>
<hr>
<h3>Responsibilities</h3>
<p>As a captain, you should familiarize yourself with the details of your league and division.
<?php echo ZULURU; ?> has many options for how spirit scores are collected, standings are calculated, playoffs are scheduled, etc.
Knowing which options are in play for your team is important.</p>
<p>You should also know who is coordinating your division.
Coordinators are listed on the "division view" page.
These are the people responsible for setting your schedule, handling spirit issues, etc.
They do their best, but things do sometimes slip through the cracks, and you need to know who to contact in these cases.</p>
