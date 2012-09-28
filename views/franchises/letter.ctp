<?php
$this->Html->addCrumb (__('Franchises', true));
$this->Html->addCrumb (sprintf(__('Starting with %s', true), $letter));
?>

<div class="franchises index">
<h2><?php __('List Franchises');?></h2>
<?php if (empty($franchises)): ?>
<p class="warning-message">There are no franchises in the system. Please check back periodically for updates.</p>
<?php else: ?>
<p><?php
__('Locate by letter: ');
$links = array();
foreach ($letters as $l) {
	$l = up($l[0]['letter']);
	if ($l != $letter) {
		$links[] = $this->Html->link($l, array('action' => 'letter', 'letter' => $l));
	} else {
		$links[] = $letter;
	}
}
echo implode ('&nbsp;&nbsp;', $links);
?></p>
<table class="list">
<tr>
	<th><?php __('Name');?></th>
	<th><?php __('Owner(s)');?></th>
	<th class="actions"><?php __('Actions');?></th>
</tr>
<?php
$i = 0;
foreach ($franchises as $franchise):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $this->Html->link($franchise['Franchise']['name'], array('action' => 'view', 'franchise' => $franchise['Franchise']['id'])); ?>
			<?php // TODO: Link to website, if any ?>
		</td>
		<td>
			<?php
			$owners = array();
			foreach ($franchise['Person'] as $person) {
				$owners[] = $this->element('people/block', compact('person'));
			}
			echo implode(', ', $owners);
			?>
		</td>
		<td class="actions">
			<?php
			if ($is_admin || in_array($franchise['Franchise']['id'], $this->Session->read('Zuluru.FranchiseIDs'))) {
				echo $this->ZuluruHtml->iconLink('edit_24.png',
					array('action' => 'edit', 'franchise' => $franchise['Franchise']['id'], 'return' => true),
					array('alt' => __('Edit Franchise', true), 'title' => __('Edit Franchise', true)));
				echo $this->ZuluruHtml->iconLink('team_add_24.png',
					array('action' => 'add_team', 'franchise' => $franchise['Franchise']['id']),
					array('alt' => __('Add Team', true), 'title' => __('Add Team', true)));
				echo $this->ZuluruHtml->iconLink('move_24.png',
					array('action' => 'add_owner', 'franchise' => $franchise['Franchise']['id']),
					array('alt' => __('Add Owner', true), 'title' => __('Add an Owner', true)));
			}
			if ($is_admin) {
				echo $this->ZuluruHtml->iconLink('delete_24.png',
					array('action' => 'delete', 'franchise' => $franchise['Franchise']['id'], 'return' => true),
					array('alt' => __('Delete', true), 'title' => __('Delete Franchise', true)),
					array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $franchise['Franchise']['id'])));
			}
			?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
</div>
