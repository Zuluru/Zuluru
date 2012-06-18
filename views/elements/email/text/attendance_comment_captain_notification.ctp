Dear <?php echo $captains; ?>,

<?php echo $person['full_name']; ?> has <?php
if (empty($comment)):
?>removed the comment from <?php
else:
?>added the following comment to <?php
endif;
?>their attendance at the <?php echo $team['name']; ?> game<?php
if (isset($game)) {
	$url = Router::url(array('controller' => 'fields', 'action' => 'view', 'field' => $game['GameSlot']['Field']['id']), true);
	echo ' against ' . $opponent['name'] .
		" at {$game['GameSlot']['Field']['long_name']} ($url)" .
		' starting at ' . $this->ZuluruTime->time($game['GameSlot']['game_start']);
	$arg = 'game';
	$val = $game['Game']['id'];
} else {
	$arg = 'date';
	$val = $date;
}
?> on <?php
echo $this->ZuluruTime->date($date);
?>.

<?php if (!empty($comment)): ?>
<?php echo $comment; ?>


<?php endif; ?>
Thanks,
<?php echo Configure::read('email.admin_name'); ?>

<?php echo Configure::read('organization.short_name'); ?> web team
