<p>The "My Teams" section of the <?php echo ZULURU; ?> home page provides a list of the teams you are on, limited to leagues that are either ongoing, closed recently, or will open soon.</p>
<p>Clicking the team name will take you to the team details and roster page.</p>
<p>To change your role on the team, including removing yourself from the roster, click on the role currently listed in parentheses after the team name. Note that you can typically only demote yourself through this method; promoting players to greater levels of responsibility must be done by the captain.</p>
<p>Along with the team name and your role, there will always be <?php
echo $this->ZuluruHtml->icon('schedule_24.png');
?> "Schedule" and <?php
echo $this->ZuluruHtml->icon('standings_24.png');
?> "Standings" links.</p>
<p>If attendance tracking is enabled for the team, there will be an <?php
echo $this->ZuluruHtml->icon('attendance_24.png');
?> "Attendance Report" link here that will show you the a summary of attendance for the team across the entire season.</p>
<p>If you are a captain of the team, there will be an <?php
echo $this->ZuluruHtml->icon('edit_24.png');
?> "Edit" link here that will allow you to edit the team details.</p>
<p>If you are a captain of the team, and the roster deadline has not yet passed, there will be an <?php
echo $this->ZuluruHtml->icon('roster_add_24.png');
?> "Add Player" link here that will allow you to add players through a variety of means.</p>
