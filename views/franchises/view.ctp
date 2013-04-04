<?php
$this->Html->addCrumb (__('Franchises', true));
$this->Html->addCrumb ($franchise['Franchise']['name']);
$this->Html->addCrumb (__('View', true));
?>

<?php
// Perhaps remove manager status, if we're looking at a different affiliate
if ($is_manager && !in_array($franchise['Franchise']['affiliate_id'], $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
	$is_manager = false;
}
?>

<div class="franchises view">
<h2><?php echo $franchise['Franchise']['name'];?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<?php if (count($affiliates) > 1): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Affiliate'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($franchise['Affiliate']['name'], array('controller' => 'affiliates', 'action' => 'view', 'affiliate' => $franchise['Affiliate']['id'])); ?>

		</dd>
		<?php endif; ?>
		<?php
		$owners = array();
		foreach ($franchise['Person'] as $person) {
			$owners[] = $this->element('people/block', compact('person'));
		}
		?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __(count($owners) > 1 ? 'Owners' : 'Owner'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo implode(', ', $owners); ?>

		</dd>
		<?php if (Configure::read('feature.urls') && !empty ($franchise['Franchise']['website'])):?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Website'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($franchise['Franchise']['website'], $franchise['Franchise']['website']); ?>

		</dd>
		<?php endif; ?>
	</dl>
</div>
<div class="actions">
	<ul>
		<?php
		$franchises = $this->Session->read('Zuluru.FranchiseIDs');
		$is_owner = is_array($franchises) && in_array($franchise['Franchise']['id'], $franchises);
		if ($is_owner) {
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('team_add_32.png',
				array('action' => 'add_team', 'franchise' => $franchise['Franchise']['id']),
				array('alt' => __('Add Team', true), 'title' => __('Add Team', true))));
		}
		if ($is_admin || $is_manager || $is_owner) {
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('edit_32.png',
				array('action' => 'edit', 'franchise' => $franchise['Franchise']['id']),
				array('alt' => __('Edit Franchise', true), 'title' => __('Edit Franchise', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('move_32.png',
				array('action' => 'add_owner', 'franchise' => $franchise['Franchise']['id']),
				array('alt' => __('Add Owner', true), 'title' => __('Add an Owner', true))));
		}
		if ($is_admin || $is_manager) {
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('delete_32.png',
				array('action' => 'delete', 'franchise' => $franchise['Franchise']['id']),
				array('alt' => __('Delete', true), 'title' => __('Delete Franchise', true)),
				array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $franchise['Franchise']['id']))));
		}
		?>
	</ul>
</div>

<?php if ($is_logged_in):?>
<div class="related">
	<?php
	$cols = 5;
	$warning = false;
	?>
	<table class="list">
	<tr>
		<th><?php __('Team'); ?></th>
		<th><?php __('Division'); ?></th>
		<th><?php __('Actions'); ?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($franchise['Team'] as $team):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $this->element('teams/block', compact('team'));?></td>
		<td><?php
		if (array_key_exists('id', $team['Division'])) {
			echo $this->element('divisions/block', array('division' => $team['Division'], 'field' => 'full_league_name'));
		} else {
			__('Unassigned');
		}
		?></td>
		<td class="actions">
		<?php
		if ($is_admin || $is_manager || $is_owner) {
			echo $this->ZuluruHtml->iconLink('delete_24.png',
				array('action' => 'remove_team', 'franchise' => $franchise['Franchise']['id'], 'team' => $team['id']),
				array('alt' => __('Remove', true), 'title' => __('Remove Team from this Franchise', true)),
				array(),
				sprintf(__('Are you sure you want to remove this %s?', true), __('team', true)));
		}
		echo $this->element('teams/actions', array('team' => $team, 'division' => $team['Division'], 'league' => $team['Division']['League'], 'is_manager' => $is_manager));
		?>
		</td>
	</tr>
	<?php endforeach; ?>
	</table>

</div>
<?php endif; ?>
