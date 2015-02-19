<?php
if (isset($division)) {
	$this->Html->addCrumb (__('Division', true));
	$this->Html->addCrumb ($division['Division']['full_league_name']);
} else {
	$this->Html->addCrumb (__('League', true));
	$this->Html->addCrumb ($league['League']['full_name']);
}
$this->Html->addCrumb (__('Delete Games', true));
?>

<div class="schedules delete">
<h2><?php
echo __('Delete Games', true) . ': ';
if (isset($division)) {
	echo $division['Division']['full_league_name'];
} else {
	echo $league['League']['full_name'];
}
?></h2>

<?php
$published = Set::extract ('/Game[published=1]', $games);
$finalized = Set::extract ('/Game[home_score>-1]', $games);

if (isset($date)) {
	$dates = Set::extract('/GameSlot/game_date', $games);
	$first = min($dates);
	$last = max($dates);
	echo $this->Html->para(null, sprintf(__('You have requested to delete games on %s.', true), $this->ZuluruTime->displayRange($first, $last)));
} else {
	echo $this->Html->para(null, sprintf(__('You have requested to delete games from pool %s.', true), $pool['Pool']['name']));
}
?>
<p><?php
printf(__('This will remove %d games', true), count($games));
if (!empty ($published)) {
	printf(__(', of which %d are published', true), count($published));
	if (!empty ($finalized)) {
		printf(__(' and %d have been finalized', true), count($finalized));
	}
}
?>.<?php
if (!empty ($same_pool)) {
	echo ' ';
	printf(__('There are %d games in the same pool but on different days which will also be deleted.', true), count($same_pool));
}
if (!empty ($dependent)) {
	echo ' ';
	printf(__('There are also %d additional games dependent in some way on these which will be deleted.', true), count($dependent));
}
?></p>
<?php
if (!empty ($published)) {
	echo $this->Html->para(null, printf(__('Deleting published games can be confusing for coaches, captains and players, so be sure to %s to inform them of this.', true),
		isset($division) ? $this->Html->link (__('contact all coaches and captains', true), array('controller' => 'divisions', 'action' => 'emails', 'division' => $division['Division']['id'])) : __('contact all coaches and captains')));
}
if (!empty ($finalized)): ?>
<p class="warning-message"><?php __('Deleting finalized games will have effects on standings <strong>which cannot be undone</strong>. Please be <strong>very sure</strong> that you want to do this before proceeding.'); ?></p>
<?php endif; ?>

<div class="actions">
<ul><li>
<?php
if (isset($division)) {
	$id_field = 'division';
	$id = $division_id;
} else {
	$id_field = 'league';
	$id = $league_id;
}
echo $this->Html->link (__('Proceed', true), array($id_field => $id, 'date' => $date, 'pool' => $pool_id, 'confirm' => true));
?>
</li></ul>
</div>
