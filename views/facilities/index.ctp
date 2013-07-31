<?php
$this->Html->addCrumb (__('Facilities', true));
$this->Html->addCrumb (__('List', true));
?>

<?php
// Perhaps remove manager status, if we're looking at a different affiliate
if ($is_manager) {
	$affiliates = array_unique(Set::extract('/Region/affiliate_id', $regions));
	$mine = array_intersect($affiliates, $this->Session->read('Zuluru.ManagedAffiliateIDs'));
	if (empty($mine)) {
		$is_manager = false;
	}
} else {
	$mine = array();
}
?>

<div class="facilities index">
<h2><?php __($closed ? 'Closed Facilities List' : 'Facilities List');?></h2>
<?php
if ($is_admin || $is_manager) {
	$set_to_test = Set::extract('/Facility/id', $regions);
} else {
	$set_to_test = $facilities_with_fields;
}
if (empty($set_to_test)):
?>
<p class="warning-message">There are no facilities currently open. Please check back periodically for updates.</p>
<?php else: ?>
<?php if (!$closed) echo $this->element('fields/caution'); ?>

<p>There is also a <?php echo $this->Html->link(sprintf(__('map of all %s', true), __(Configure::read('ui.fields'), true)), array('controller' => 'maps'), array('target' => 'map')); ?> available.</p>

<?php if ($is_admin || $is_manager): ?>
<?php if ($closed): ?>
<p class="highlight-message">This list shows facilities which are closed, or which have at least one closed <?php __(Configure::read('ui.field')); ?>.
Opening a facility leaves all <?php __(Configure::read('ui.fields')); ?> at that facility closed; they must be individually opened through the "facility view" page.</p>
<?php else: ?>
<p class="highlight-message">This list shows only facilities which are open, and which also have open <?php __(Configure::read('ui.fields')); ?>.
Closing a facility closes all <?php __(Configure::read('ui.fields')); ?> at that facility, and should only be done when a facility is no longer going to be in use.</p>
<?php endif; ?>
<?php endif; ?>

<table class="list">
<tr>
	<th><?php __(Configure::read('ui.field_cap')); ?></th>
	<th><?php __('Actions'); ?></th>
</tr>

<?php
$i = 0;
$affiliate_id = null;
foreach ($regions as $region):
	$is_manager = in_array($region['Region']['affiliate_id'], $mine);
	$ids = Set::extract('/Facility/id', $region);
	if (!$is_admin && !$is_manager) {
		// We only want to list facilities without fields for administrators
		foreach ($ids as $key => $id) {
			if (!array_key_exists($id, $facilities_with_fields)) {
				unset($ids[$key]);
			}
		}
	}
	if (empty($ids)) {
		continue;
	}

	if (count($affiliates) > 1 && $region['Region']['affiliate_id'] != $affiliate_id):
		$affiliate_id = $region['Region']['affiliate_id'];
?>
<tr>
	<th colspan="2">
		<h3 class="affiliate"><?php echo $region['Affiliate']['name']; ?></h3>
	</th>
</tr>
<?php
	endif;

	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
	if (count($regions) > 1) {
		echo "<tr$class><td colspan='2'><h4>{$region['Region']['name']}</h4></td></tr>";
	}

	foreach ($region['Facility'] as $facility):
		if (empty($facility['Field']) && (!($is_admin || $is_manager) || array_key_exists($facility['id'], $facilities_with_fields))) {
			continue;
		}
		$surfaces = array_unique(Set::extract('/Field/surface', $facility));

		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $this->Html->link(__($facility['name'], true), array('controller' => 'facilities', 'action' => 'view', 'facility' => $facility['id'])); ?>
			<?php if (!empty($surfaces)) echo '[' . implode('/', $surfaces) . ']'; ?>
		</td>
		<td class="actions">
			<?php
			if (!empty($facility['Field'])) {
				echo $this->Html->link(__('Layout', true), array('controller' => 'maps', 'action' => 'view', 'field' => $facility['Field'][0]['id']), array('target' => 'map'));
			}
			?>
		<?php if ($is_admin || $is_manager): ?>
			<?php echo $this->ZuluruHtml->iconLink('edit_24.png',
					array('action' => 'edit', 'facility' => $facility['id'], 'return' => true),
					array('alt' => __('Edit', true), 'title' => __('Edit', true))); ?>
			<?php echo $this->ZuluruHtml->iconLink('add_24.png',
					array('controller' => 'fields', 'action' => 'add', 'facility' => $facility['id'], 'return' => true),
					array('alt' => sprintf(__('Add %s', true), __(Configure::read('ui.field'), true)), 'title' => sprintf(__('Add %s', true), __(Configure::read('ui.field'), true)))); ?>
			<?php echo $this->ZuluruHtml->iconLink('delete_24.png',
					array('action' => 'delete', 'facility' => $facility['id'], 'return' => true),
					array('alt' => __('Delete', true), 'title' => __('Delete', true)),
					array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $facility['id']))); ?>
			<span id="span_<?php echo $facility['id']; ?>">
			<?php
			if ($facility['is_open']) {
				echo $this->Js->link(__('Close', true),
						array('controller' => 'facilities', 'action' => 'close', 'facility' => $facility['id']),
						array('update' => "#temp_update")
				);
			} else {
				echo $this->Js->link(__('Open', true),
						array('controller' => 'facilities', 'action' => 'open', 'facility' => $facility['id']),
						array('update' => "#temp_update")
				);
			}
			?>
			</span>
		<?php endif; ?>
		</td>
	</tr>
	<?php endforeach; ?>
<?php endforeach; ?>

</table>
<?php endif; ?>
</div>
