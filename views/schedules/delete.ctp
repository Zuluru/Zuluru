<?php
$this->Html->addCrumb (__('Division', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('Delete Games', true));
?>

<div class="schedules delete">
<h2><?php  echo __('Delete Games', true) . ': ' . $division['Division']['full_league_name'];?></h2>

<?php
$published = Set::extract ('/Game[published=1]', $games);
$finalized = Set::extract ('/Game[home_score>-1]', $games);
?>

<p>You have requested to delete games
<?php if (isset($date)): ?>
on <?php echo $this->ZuluruTime->date($date); ?>
<?php else: ?>
from pool <?php echo $pool['Pool']['name']; ?>
<?php endif; ?>.</p>
<p>This will remove <?php echo count($games); ?> games<?php
if (!empty ($published)): ?>
, of which <?php echo count($published); ?> are published<?php
if (!empty ($finalized)): ?>
 and <?php echo count($finalized); ?> have been finalized
<?php endif; ?>
<?php endif; ?>
.<?php if (!empty ($same_pool)): ?>
 There are <?php echo count($same_pool); ?> games in the same pool but on different days which will also be deleted.<?php endif; ?>
<?php if (!empty ($dependent)): ?>
 There are also <?php echo count($dependent); ?> additional games dependent in some way on these which will be deleted.<?php endif; ?></p>
<?php if (!empty ($published)): ?>
<p>Deleting published games can be confusing for players and captains, so be sure to <?php
echo $this->Html->link (__('contact all captains', true), array('controller' => 'divisions', 'action' => 'emails', 'division' => $id));
?> to inform them of this.</p>
<?php endif; ?>
<?php if (!empty ($finalized)): ?>
<p class="warning-message">Deleting finalized games will have effects on standings <strong>which cannot be undone</strong>. Please be <strong>very sure</strong> that you want to do this before proceeding.</p>
<?php endif; ?>

<div class="actions">
<ul><li>
<?php echo $this->Html->link (__('Proceed', true), array('division' => $id, 'date' => $date, 'pool' => $pool_id, 'confirm' => true)); ?>
</li></ul>
</div>
