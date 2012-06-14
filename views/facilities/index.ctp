<?php
$this->Html->addCrumb (__('Facilities', true));
$this->Html->addCrumb (__('List', true));
?>

<div class="facilities index">
<h2><?php __($closed ? 'Closed Facilities List' : 'Facilities List');?></h2>
<?php if (!$closed) echo $this->element('fields/caution'); ?>

<p>There is also a <?php echo $this->Html->link(sprintf(__('map of all %s', true), __(Configure::read('ui.fields'), true)), array('controller' => 'maps')); ?> available.</p>

<?php if ($is_admin): ?>
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
foreach ($regions as $region):
	$ids = Set::extract('/Facility/id', $region);
	if (!$is_admin) {
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

	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
	echo "<tr$class><td colspan='2'><h3>{$region['Region']['name']}</h3></td></tr>";

	foreach ($region['Facility'] as $facility):
		if (empty($facility['Field']) && (!$is_admin || array_key_exists($facility['id'], $facilities_with_fields))) {
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
			[<?php echo implode('/', $surfaces); ?>]
		</td>
		<td class="actions">
			<?php
			if (!empty($facility['Field'])) {
				echo $this->Html->link(__('Layout', true), array('controller' => 'maps', 'action' => 'view', 'field' => $facility['Field'][0]['id']), array('target' => '_new'));
			}
			?>
<?php if ($is_admin): ?>
			<?php echo $this->Html->link(sprintf(__('Edit %s', true), __('Facility', true)), array('controller' => 'facilities', 'action' => 'edit', 'facility' => $facility['id'])); ?>
			<?php echo $this->Html->link(sprintf(__('Add %s', true), __(Configure::read('ui.field'), true)), array('controller' => 'fields', 'action' => 'add', 'facility' => $facility['id'])); ?>
			<?php $id = 'span_' . mt_rand(); ?>
			<span id="<?php echo $id; ?>">
			<?php
			if ($facility['is_open']) {
				echo $this->Js->link(__('Close', true),
						array('controller' => 'facilities', 'action' => 'close', 'facility' => $facility['id'], 'id' => $id),
						array('update' => "#temp_update")
				);
			} else {
				echo $this->Js->link(__('Open', true),
						array('controller' => 'facilities', 'action' => 'open', 'facility' => $facility['id'], 'id' => $id),
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
</div>
