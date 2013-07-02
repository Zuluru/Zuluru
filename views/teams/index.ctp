<?php
$this->Html->addCrumb (__('Teams', true));
$this->Html->addCrumb (__('List', true));
?>

<div class="teams index">
<h2><?php __('List Teams');?></h2>
<?php if (empty($teams)): ?>
<p class="warning-message">There are no teams currently running. Please check back periodically for updates.</p>
<?php else: ?>
<p><?php
__('Locate by letter: ');
$links = array();
foreach ($letters as $l) {
	$l = up($l[0]['letter']);
	$links[] = $this->Html->link($l, array('action' => 'letter', 'affiliate' => $affiliate, 'letter' => $l));
}
echo implode ('&nbsp;&nbsp;', $links);
?></p>
<p>
<?php
echo $this->Paginator->counter(array(
'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
));
?></p>
<table class="list">
<tr>
	<th><?php echo $this->Paginator->sort('name');?></th>
	<th><?php echo $this->Paginator->sort('division_id');?></th>
	<th class="actions"><?php __('Actions');?></th>
</tr>
<?php
$i = 0;
$affiliate_id = null;

$leagues = array_unique(Set::extract('/League/name', $teams));
if (count($leagues) == 1) {
	$field = 'name';
} else {
	$field = 'full_league_name';
}

foreach ($teams as $team):
	$is_manager = $is_logged_in && in_array($team['League']['affiliate_id'], $this->Session->read('Zuluru.ManagedAffiliateIDs'));
	Division::_addNames($team['Division'], $team['League']);

	if (count($affiliates) > 1 && $team['League']['affiliate_id'] != $affiliate_id):
		$affiliate_id = $team['League']['affiliate_id'];
?>
<tr>
	<th colspan="3">
		<h3 class="affiliate"><?php echo $team['Affiliate']['name']; ?></h3>
	</th>
</tr>
<?php
	endif;

	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $this->element('teams/block', array('team' => $team['Team'])); ?>
		</td>
		<td>
			<?php echo $this->element('divisions/block', array('division' => $team['Division'], 'field' => $field)); ?>
		</td>
		<td class="actions">
			<?php echo $this->element('teams/actions', array('team' => $team['Team'], 'division' => $team['Division'], 'league' => $team['League'], 'format' => 'links')); ?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
</div>
<div class="paging">
	<?php echo $this->Paginator->prev('<< '.__('previous', true), array(), null, array('class'=>'disabled'));?>
 | 	<?php echo $this->Paginator->numbers();?> | 
	<?php echo $this->Paginator->next(__('next', true).' >>', array(), null, array('class' => 'disabled'));?>
<?php endif; ?>
</div>
