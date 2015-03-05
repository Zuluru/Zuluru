<p><?php printf(__('The "My Teams" section of the %s home page provides a list of the teams you are on, limited to leagues that are either ongoing, closed recently, or will open soon.', true), ZULURU); ?></p>
<p><?php __('Clicking the team name will take you to the team details and roster page.'); ?></p>
<p><?php __('To change your role on the team, including removing yourself from the roster, click on the role currently listed in parentheses after the team name. Note that you can typically only demote yourself through this method; promoting players to greater levels of responsibility must be done by a coach or captain.'); ?></p>
<p><?php
printf(__('Along with the team name and your role, there will always be %s "Schedule" and %s "Standings" links.', true),
	$this->ZuluruHtml->icon('schedule_24.png'),
	$this->ZuluruHtml->icon('standings_24.png')
); ?></p>
<p><?php
printf(__('If attendance tracking is enabled for the team, there will be an %s "Attendance Report" link here that will show you the a summary of attendance for the team across the entire season.', true),
	$this->ZuluruHtml->icon('attendance_24.png')
); ?></p>
<p><?php
printf(__('If you are a coach or captain of the team, there will be an %s "Edit" link here that will allow you to edit the team details.', true),
	$this->ZuluruHtml->icon('edit_24.png')
); ?></p>
<p><?php
printf(__('If you are a coach or captain of the team, and the roster deadline has not yet passed, there will be an %s "Add Player" link here that will allow you to add players through a variety of means.', true),
	$this->ZuluruHtml->icon('roster_add_24.png')
); ?></p>
