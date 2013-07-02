<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb (__('Approve Badges', true));
?>

<div class="people badges">
<h2><?php __('Approve Badges'); ?></h2>

<table class="list">
	<th><?php __('Badge'); ?></th>
	<th><?php __('Player'); ?></th>
	<th><?php __('Reason'); ?></th>
	<th><?php __('Actions'); ?></th>
<?php
$i = 0;
$affiliate_id = null;
foreach ($badges as $badge):
	if (count($affiliates) > 1 && $badge['Badge']['affiliate_id'] != $affiliate_id):
		$affiliate_id = $badge['Badge']['affiliate_id'];
?>
<tr>
	<th colspan="4">
		<h3 class="affiliate"><?php echo $team['Affiliate']['name']; ?></h3>
	</th>
</tr>
<?php
	endif;

	foreach ($badge['Person'] as $person):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
		$rand = 'row_' . mt_rand();
?>
<tr id="<?php echo $rand; ?>"<?php echo $class;?>>
	<td><?php echo $this->element('badges/block', array('badge' => $badge, 'use_name' => true)); ?></td>
	<td><?php echo $this->element('people/block', compact('person')); ?></td>
	<td><?php echo $person['BadgesPerson']['reason']; ?></td>
	<td class="actions"><?php
		echo $this->Js->link (__('Approve', true),
				array('controller' => 'people', 'action' => 'approve_badge', 'id' => $person['BadgesPerson']['id'], 'row' => $rand),
				array('update' => "#temp_update"));
		$url = array('action' => 'delete_badge', 'id' => $person['BadgesPerson']['id'], 'row' => $rand);
		$url_string = Router::url($url);
		echo $this->Html->link(__('Delete', true), $url,
				array(
					'escape' => false,
					'onClick' => "badge_handle_comment('$url_string'); return false;",
				)
		);
	?></td>
</tr>
<?php
	endforeach;
endforeach;
?>
</table>
</div>

<?php echo $this->element('people/badge_div', array(
	'message' => 'If you want to add a comment to the nominator about why the nomination was not approved, do so here. The nominee will not receive any message.',
)); ?>
