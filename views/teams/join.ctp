<?php
$this->Html->addCrumb (__('Teams', true));
$this->Html->addCrumb (__('Join', true));
?>

<div class="teams index" id="kick_start">
<h2><?php __('Join a Team');?></h2>

<p>
<?php
echo $this->Paginator->counter(array(
'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
));
?></p>

<div class="actions">
<ul>
<?php
$affiliate_id = null;
foreach ($teams as $team) {
	$is_manager = in_array($team['League']['affiliate_id'], $this->Session->read('Zuluru.ManagedAffiliateIDs'));
	Division::_addNames($team['Division'], $team['League']);

	if (count($affiliates) > 1 && $team['League']['affiliate_id'] != $affiliate_id):
		$affiliate_id = $team['League']['affiliate_id'];
?>
	<h3 class="affiliate"><?php echo $team['Affiliate']['name']; ?></h3>
<?php
	endif;

	if (!in_array($team['Team']['id'], $this->Session->read('Zuluru.TeamIDs'))) {
		echo $this->Html->tag('li', $this->Html->link($team['Team']['name'] . ' (' . $team['Division']['league_name'] . ')',
				array('controller' => 'teams', 'action' => 'roster_request', 'team' => $team['Team']['id'])));
	}
}
?>
</ul>
</div>

<div class="paging">
	<?php echo $this->Paginator->prev('<< '.__('previous', true), array(), null, array('class'=>'disabled'));?>
 | 	<?php echo $this->Paginator->numbers();?> | 
	<?php echo $this->Paginator->next(__('next', true).' >>', array(), null, array('class' => 'disabled'));?>
</div>

<p class="warning-message">If you don't see the team you're looking for, the captain may have made the roster "closed", so that they have to invite you to join the team. Contact them directly to let them know you've signed up and are ready to play!</p>
</div>
