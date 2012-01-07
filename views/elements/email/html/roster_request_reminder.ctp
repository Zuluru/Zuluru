<p>Dear <?php echo $captains; ?>,</p>
<p><?php echo $person['full_name']; ?> has requested to join the roster of the <?php
echo Configure::read('organization.name'); ?> team <?php echo $team['name']; ?> as a <?php
echo Configure::read("options.roster_position.${roster['position']}"); ?>.</p>
<p>The <?php echo $team['name']; ?> roster may be accessed at
<?php
$url = Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']), true);
echo $this->Html->link($url, $url);
?></p>
<p>You need to be logged into the website to update this.</p>
<p>We ask that you please accept or decline this request at your earliest convenience. The request will expire <?php echo $days; ?> days from now.</p>
<p>If you accept the request, <?php echo $person['first_name']; ?> will be added to the team's roster as a <?php
echo Configure::read("options.roster_position.${roster['position']}"); ?>. You have the option of changing their position on the team afterwards.</p>
<p>Accept the request here:
<?php
$url = Router::url(array('controller' => 'teams', 'action' => 'roster_accept', 'team' => $team['id'], 'person' => $person['id'], 'code' => $code), true);
echo $this->Html->link($url, $url);
?></p>
<p>If you decline the request they will be removed from this team's roster.</p>
<p>Decline the request here:
<?php
$url = Router::url(array('controller' => 'teams', 'action' => 'roster_decline', 'team' => $team['id'], 'person' => $person['id'], 'code' => $code), true);
echo $this->Html->link($url, $url);
?></p>
<p>Please be advised that players are NOT considered a part of a team roster until their request to join has been accepted by a captain. The <?php
echo $team['name']; ?> roster must be completed (minimum of <?php
echo Configure::read("roster_requirements.{$division['ratio']}"); ?> rostered players) by the team roster deadline (<?php
$date_format = array_shift (Configure::read('options.date_formats'));
echo $this->Time->format($date_format, $division['roster_deadline']);
?>), and all team members must have been accepted by the captain.</p>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
