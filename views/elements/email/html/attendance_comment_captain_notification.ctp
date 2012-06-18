<p>Dear <?php echo $captains; ?>,</p>
<p><?php echo $person['full_name']; ?> has <?php
if (empty($comment)):
?>removed the comment from <?php
else:
?>added the following comment to <?php
endif;
?>their attendance at the <?php
$url = Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']), true);
echo $this->Html->link($team['name'], $url);
?> game<?php
if (isset($game)) {
	$url = Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $opponent['id']), true);
	echo ' against ' . $this->Html->link($opponent['name'], $url);

	$url = Router::url(array('controller' => 'fields', 'action' => 'view', 'field' => $game['GameSlot']['Field']['id']), true);
	echo ' at ' . $this->Html->link($game['GameSlot']['Field']['long_name'], $url);

	$url = Router::url(array('controller' => 'games', 'action' => 'view', 'game' => $game['Game']['id']), true);
	echo ' starting at ' . $this->Html->link($this->ZuluruTime->time($game['GameSlot']['game_start']), $url);
}
?> on <?php
echo $this->ZuluruTime->date($date);
?>.</p>
<?php if (!empty($comment)): ?>
<p><?php echo $comment; ?></p>
<?php endif; ?>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
