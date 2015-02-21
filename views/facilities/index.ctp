<?php
$this->Html->addCrumb (__('Facilities', true));
$this->Html->addCrumb (__('List', true));
?>

<?php
// Perhaps remove manager status, if we're looking at a different affiliate
if ($is_manager) {
	$affiliates = array_unique(Set::extract('/Region/affiliate_id', $regions));
	$mine = array_intersect($affiliates, $this->UserCache->read('ManagedAffiliateIDs'));
	if (empty($mine)) {
		$is_manager = false;
	}
} else {
	$mine = array();
}
?>

<div class="facilities index">
<h2><?php $closed ? __('Closed Facilities List') : __('Facilities List');?></h2>
<?php
if ($is_admin || $is_manager) {
	$set_to_test = Set::extract('/Facility/id', $regions);
} else {
	$set_to_test = $facilities_with_fields;
}
if (empty($set_to_test)):
	echo $this->Html->para('warning-message', __('There are no facilities currently open. Please check back periodically for updates.'));
else:
	if (!$closed) echo $this->element('fields/caution');
	echo $this->Html->para(null, sprintf(__('There is also a %s available.', true),
			$this->Html->link(sprintf(__('map of all %s', true), __(Configure::read('ui.fields'), true)), array('controller' => 'maps'), array('target' => 'map'))
	));

	if ($is_admin || $is_manager) {
		if ($closed) {
			echo $this->Html->para('highlight-message', sprintf(__('This list shows facilities which are closed, or which have at least one closed %s. Opening a facility leaves all %s at that facility closed; they must be individually opened through the "facility view" page.', true),
					__(Configure::read('ui.field'), true), __(Configure::read('ui.fields'), true)
			));
		} else {
			echo $this->Html->para('highlight-message', sprintf(__('This list shows only facilities which are open, and which also have open %s. Closing a facility closes all %s at that facility, and should only be done when a facility is no longer going to be in use.', true),
					__(Configure::read('ui.fields'), true), __(Configure::read('ui.fields'), true)
			));
		}
	}
?>

<?php
$sports = array_unique(Set::extract('/Facility/Field/sport', $regions));
sort($sports);
echo $this->element('selector', array(
		'title' => 'Sport',
		'options' => $sports,
));

$surfaces = array_unique(Set::extract('/Facility/Field/surface', $regions));
sort($surfaces);
echo $this->element('selector', array(
		'title' => 'Surface',
		'options' => $surfaces,
));

$indoor = array_flip(array_unique(Set::extract('/Facility/Field/indoor', $regions)));
$indoor_options = array(1 => 'indoor', 0 => 'outdoor');
echo $this->element('selector', array(
		'title' => 'Indoor/Outdoor',
		'options' => array_intersect_key($indoor_options, $indoor),
));
?>

<table class="list">
<tr>
	<th><?php __('Facility'); ?></th>
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

		$affiliate_sports = array_unique(Set::extract("/Region[affiliate_id=$affiliate_id]/Facility/Field/sport", $regions));
		$affiliate_surfaces = array_unique(Set::extract("/Region[affiliate_id=$affiliate_id]/Facility/Field/surface", $regions));
		$affiliate_indoor = array_flip(array_unique(Set::extract("/Region[affiliate_id=$affiliate_id]/Facility/Field/indoor", $regions)));
?>
<tr class="<?php echo $this->element('selector_classes', array('title' => 'Sport', 'options' => $affiliate_sports)); ?> <?php echo $this->element('selector_classes', array('title' => 'Surface', 'options' => $affiliate_surfaces)); ?> <?php echo $this->element('selector_classes', array('title' => 'Indoor/Outdoor', 'options' => array_intersect_key($indoor_options, $affiliate_indoor))); ?>">
	<th colspan="2">
		<h3 class="affiliate"><?php echo $region['Affiliate']['name']; ?></h3>
	</th>
</tr>
<?php
	endif;

	if (count($regions) > 1):
		$region_sports = array_unique(Set::extract('/Facility/Field/sport', $region));
		$region_surfaces = array_unique(Set::extract('/Facility/Field/surface', $region));
		$region_indoor = array_flip(array_unique(Set::extract('/Facility/Field/indoor', $region)));

		$class = null;
		if ($i++ % 2 == 0) {
			$class = 'altrow ';
		}
?>
<tr class="<?php echo $class; echo $this->element('selector_classes', array('title' => 'Sport', 'options' => $region_sports)); ?> <?php echo $this->element('selector_classes', array('title' => 'Surface', 'options' => $region_surfaces)); ?> <?php echo $this->element('selector_classes', array('title' => 'Indoor/Outdoor', 'options' => array_intersect_key($indoor_options, $region_indoor))); ?>">
	<td colspan="2">
		<h4 class="affiliate"><?php echo $region['Region']['name']; ?></h4>
	</td>
</tr>
<?php
	endif;

	foreach ($region['Facility'] as $facility):
		if (empty($facility['Field']) && (!($is_admin || $is_manager) || array_key_exists($facility['id'], $facilities_with_fields))) {
			continue;
		}
		$facility_sports = array_unique(Set::extract('/Field/sport', $facility));
		$facility_surfaces = array_unique(Set::extract('/Field/surface', $facility));
		$facility_indoor = array_flip(array_unique(Set::extract('/Field/indoor', $facility)));

		$class = null;
		if ($i++ % 2 == 0) {
			$class = 'altrow ';
		}
?>
	<tr class="<?php echo $class; echo $this->element('selector_classes', array('title' => 'Sport', 'options' => $facility_sports)); ?> <?php echo $this->element('selector_classes', array('title' => 'Surface', 'options' => $facility_surfaces)); ?> <?php echo $this->element('selector_classes', array('title' => 'Indoor/Outdoor', 'options' => array_intersect_key($indoor_options, $facility_indoor))); ?>">
		<td>
			<?php echo $this->Html->link($facility['name'], array('controller' => 'facilities', 'action' => 'view', 'facility' => $facility['id'])); ?>
			<?php if (!empty($facility_surfaces)) echo '[' . implode('/', $facility_surfaces) . ']'; ?>
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
