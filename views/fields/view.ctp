<?php
$this->Html->addCrumb (__('Fields', true));
$this->Html->addCrumb ($field['Field']['long_name']);
$this->Html->addCrumb (__('View', true));
?>

<div class="fields view">
<h2><?php  echo __('View Field', true) . ': ' . $field['Field']['long_name'];?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Name'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $field['Field']['name']; ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Code'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $field['Field']['code']; ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Status'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __($field['Field']['is_open'] ? 'Open' : 'Closed'); ?>

		</dd>
<?php if ($is_admin): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Rating'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $field['Field']['rating']; ?>

		</dd>
<?php endif; ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Number'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $field['Field']['num']; ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Region'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __($field['Region']['name']); ?>

		</dd>
<?php if (!empty ($field['Field']['location_street'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Address'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $field['Field']['location_street']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('City'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $field['Field']['location_city']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Province'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $field['Field']['location_province']; ?>
			&nbsp;
		</dd>
<?php endif; ?>

<?php
$mapurl = null;
if ($field['Field']['length'] > 0) {
	$mapurl = array('controller' => 'maps', 'action' => 'view', 'field' => $field['Field']['id']);
} else if (!empty ($field['Field']['location_url'])) {
    // Useful during transition period from old maps to new
    $mapurl = $field['Field']['location_url'];
}
?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Map'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo ($mapurl ? $this->Html->link (__('Click for map in new window', true), $mapurl, array('target' => '_new')) : __('N/A', true)); ?>

		</dd>
<?php if (!empty ($field['Field']['layout_url'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Layout'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link (__('Click for field layout diagram in new window', true), $field['Field']['layout_url'], array('target' => '_new')); ?>

		</dd>
<?php endif; ?>
<?php if (!empty ($field['Field']['permit_url'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Field&nbsp;Permit'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link ($field['Field']['permit_name'], $field['Field']['permit_url']); ?>

		</dd>
<?php endif; ?>
<?php if (!empty ($field['Field']['notes'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Notes'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $field['Field']['notes']; ?>

		</dd>
<?php endif; ?>
<?php if (!empty ($field['Field']['driving_directions'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Driving Directions'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $field['Field']['driving_directions']; ?>

		</dd>
<?php endif; ?>
<?php if (!empty ($field['Field']['parking_details'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Parking Details'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $field['Field']['parking_details']; ?>

		</dd>
<?php endif; ?>
<?php if (!empty ($field['Field']['transit_directions'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Transit Directions'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $field['Field']['transit_directions']; ?>

		</dd>
<?php endif; ?>
<?php if (!empty ($field['Field']['biking_directions'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Biking Directions'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $field['Field']['biking_directions']; ?>

		</dd>
<?php endif; ?>
<?php if (!empty ($field['Field']['washrooms'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Washrooms'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $field['Field']['washrooms']; ?>

		</dd>
<?php endif; ?>
<?php if (!empty ($field['Field']['public_instructions'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Special Instructions'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $field['Field']['public_instructions']; ?>

		</dd>
<?php endif; ?>
<?php if (!empty ($field['Field']['site_instructions'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Private Instructions'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php
			if ($is_logged_in) {
				echo $field['Field']['site_instructions'];
			} else {
				__('You must be logged in to see the private instructions for this site.');
			}
			?>

		</dd>
<?php endif; ?>

<?php if (!empty ($field['SiteFields'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Other fields at this site'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<table>
			<tr>
				<th>Fields</th>
			</tr>
			<?php
			foreach ($field['SiteFields'] as $siteField) {
				// TODO: Any admin links we want to add here?
				echo $this->Html->tag('tr',
						$this->Html->tag('td',
							$this->Html->link("{$field['Field']['name']} {$siteField['Field']['num']}", array('action' => 'view', 'field' => $siteField['Field']['id']))
				));
			}
			?>

			</table>
		</dd>
<?php endif; ?>
	</dl>
</div>
<?php if (!empty ($field['Field']['sponsor'])): ?>
<div class="sponsor"><?php echo $field['Field']['sponsor']; ?></div>
<?php endif; ?>

<div class="actions">
	<ul>
<?php if ($is_admin): ?>
		<li><?php echo $this->Html->link(sprintf(__('Edit %s', true), __('Field', true)), array('action' => 'edit', 'field' => $field['Field']['id'])); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('Edit %s', true), __('Layout', true)), array('controller' => 'maps', 'action' => 'edit', 'field' => $field['Field']['id'])); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('Add %s', true), __('Game Slots', true)), array('controller' => 'game_slots', 'action' => 'add', 'field' => $field['Field']['id'])); ?> </li>
<?php endif; ?>
		<li><?php echo $this->Html->link(sprintf(__('View %s', true), __('Bookings ', true)), array('controller' => 'fields', 'action' => 'bookings', 'field' => $field['Field']['id'])); ?> </li>
	</ul>
</div>
