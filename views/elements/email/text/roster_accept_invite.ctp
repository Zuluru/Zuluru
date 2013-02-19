Dear <?php echo $captains; ?>,

<?php echo $person['full_name']; ?> has accepted your invitation to join the roster of the <?php
echo Configure::read('organization.name'); ?> team <?php echo $team['name']; ?> as a <?php
echo Configure::read("options.roster_role.$role"); ?>.

The <?php echo $team['name']; ?> roster may be accessed at
<?php echo Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']), true); ?>

You need to be logged into the website to update this.

Thanks,
<?php echo Configure::read('email.admin_name'); ?>

<?php echo Configure::read('organization.short_name'); ?> web team
