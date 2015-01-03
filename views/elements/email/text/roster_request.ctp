Dear <?php echo $captains; ?>,

<?php echo $person['full_name']; ?> has requested to join the roster of the <?php
echo Configure::read('organization.name'); ?> team <?php echo $team['name']; ?> as a <?php
echo Configure::read("options.roster_role.$role"); ?>.

The <?php echo $team['name']; ?> roster may be accessed at
<?php echo Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']), true); ?>

You need to be logged into the website to update this.

We ask that you please accept or decline this request at your earliest convenience. The request will expire after a couple of weeks.

If you accept the request, <?php echo $person['first_name']; ?> will be added to the team's roster as a <?php
echo Configure::read("options.roster_role.$role"); ?>. You have the option of changing their role on the team afterwards.

Accept the request here:
<?php echo Router::url(array('controller' => 'teams', 'action' => 'roster_accept', 'team' => $team['id'], 'person' => $person['id'], 'code' => $code), true); ?>


If you decline the request they will be removed from this team's roster.

Decline the request here:
<?php echo Router::url(array('controller' => 'teams', 'action' => 'roster_decline', 'team' => $team['id'], 'person' => $person['id'], 'code' => $code), true); ?>


Please be advised that players are NOT considered a part of a team roster until their request to join has been accepted by a coach or captain. The <?php
echo $team['name']; ?> roster must be completed <?php
$min = Configure::read("sport.roster_requirements.{$division['ratio']}");
if ($min > 0): ?>(minimum of <?php echo $min; ?> rostered players) <?php endif; ?>by the team roster deadline (<?php
$date_format = reset(Configure::read('options.date_formats'));
echo $this->ZuluruTime->date(Division::rosterDeadline($division));
?>), and all team members must have been accepted by a coach or captain.

<?php echo $this->element('email/text/footer'); ?>
