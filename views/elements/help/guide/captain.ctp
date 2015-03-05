<h2><?php __('Coach/Captain Guide'); ?></h2>
<p><?php printf(__('So, you want to be a coach or captain? %s includes many tools and features to make this often thankless job much easier.', true), ZULURU); ?></p>

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
<h3><?php __('Responsibilities'); ?></h3>
<p><?php
printf(__('As a coach or captain, you should familiarize yourself with the details of your league and division. %s has many options for how%s standings are calculated, playoffs are scheduled, etc. Knowing which options are in play for your team is important.', true),
	ZULURU, (Configure::read('feature.spirit') ? __(' spirit scores are collected,', true) : '')
); ?></p>
<p><?php
printf(__('You should also know who is coordinating your division. Coordinators are listed on the "division view" page. These are the people responsible for setting your schedule,%s etc. They do their best, but things do sometimes slip through the cracks, and you need to know who to contact in these cases.', true),
	(Configure::read('feature.spirit') ? __(' handling spirit issues,', true) : '')
); ?></p>
