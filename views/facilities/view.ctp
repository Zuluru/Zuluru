<?php
$this->Html->addCrumb (__('Facilities', true));
$this->Html->addCrumb ($facility['Facility']['name']);
$this->Html->addCrumb (__('View', true));
?>

<div class="facilities view">
<h2><?php  echo __('View Facility', true) . ': ' . $facility['Facility']['name'];?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Name'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $facility['Facility']['name']; ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Code'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $facility['Facility']['code']; ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Region'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __($facility['Region']['name']); ?>

		</dd>
<?php if (!empty ($facility['Facility']['location_street'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Address'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $facility['Facility']['location_street']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('City'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $facility['Facility']['location_city']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Province'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $facility['Facility']['location_province']; ?>
			&nbsp;
		</dd>
<?php endif; ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Status'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __($facility['Facility']['is_open'] ? 'Open' : 'Closed'); ?>

		</dd>
<?php if (!empty ($facility['Facility']['driving_directions'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Driving Directions'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $facility['Facility']['driving_directions']; ?>

		</dd>
<?php endif; ?>
<?php if (!empty ($facility['Facility']['parking_details'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Parking Details'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $facility['Facility']['parking_details']; ?>

		</dd>
<?php endif; ?>
<?php if (!empty ($facility['Facility']['transit_directions'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Transit Directions'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $facility['Facility']['transit_directions']; ?>

		</dd>
<?php endif; ?>
<?php if (!empty ($facility['Facility']['biking_directions'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Biking Directions'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $facility['Facility']['biking_directions']; ?>

		</dd>
<?php endif; ?>
<?php if (!empty ($facility['Facility']['washrooms'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Washrooms'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $facility['Facility']['washrooms']; ?>

		</dd>
<?php endif; ?>
<?php if (!empty ($facility['Facility']['public_instructions'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Special Instructions'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $facility['Facility']['public_instructions']; ?>

		</dd>
<?php endif; ?>
<?php if (!empty ($facility['Facility']['site_instructions'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Private Instructions'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php
			if ($is_logged_in) {
				echo $facility['Facility']['site_instructions'];
			} else {
				__('You must be logged in to see the private instructions for this site.');
			}
			?>

		</dd>
<?php endif; ?>

<?php if (!empty ($facility['Field'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php printf(__('%s at this facility', true), __(Configure::read('ui.fields_cap'), true)); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<table class="list">
			<tr>
				<th><?php __(Configure::read('ui.field_cap')); ?></th>
<?php if ($is_admin): ?>
				<th><?php __('Actions'); ?></th>
<?php endif; ?>
			</tr>
			<?php foreach ($facility['Field'] as $related): ?>
			<tr>
				<td><?php
				echo $this->Html->link("{$facility['Facility']['name']} {$related['num']}", array('controller' => 'fields', 'action' => 'view', 'field' => $related['id']));
				?></td>
<?php if ($is_admin): ?>
				<td class="actions">
					<?php echo $this->Html->link(sprintf(__('Edit %s', true), __(Configure::read('ui.field_cap'), true)), array('controller' => 'fields', 'action' => 'edit', 'field' => $related['id'])); ?>
					<?php echo $this->Html->link(sprintf(__('Edit %s', true), __('Layout', true)), array('controller' => 'maps', 'action' => 'edit', 'field' => $related['id'])); ?>
					<?php echo $this->Html->link(sprintf(__('Add %s', true), __('Game Slots', true)), array('controller' => 'game_slots', 'action' => 'add', 'field' => $related['id'])); ?>
					<?php echo $this->Html->link(sprintf(__('View %s', true), __('Bookings', true)), array('controller' => 'fields', 'action' => 'bookings', 'field' => $related['id'])); ?>
					<?php $id = 'span_' . mt_rand(); ?>
					<span id="<?php echo $id; ?>">
					<?php
					if ($related['is_open']) {
						echo $this->Js->link(__('Close', true),
								array('controller' => 'fields', 'action' => 'close', 'field' => $related['id'], 'id' => $id),
								array('update' => "#temp_update")
						);
					} else {
						echo $this->Js->link(__('Open', true),
								array('controller' => 'fields', 'action' => 'open', 'field' => $related['id'], 'id' => $id),
								array('update' => "#temp_update")
						);
					}
					?>
					</span>
				</td>
<?php endif; ?>
			</tr>
			<?php endforeach; ?>

			</table>
		</dd>
<?php endif; ?>
	</dl>
</div>
<?php if (!empty ($facility['Facility']['sponsor'])): ?>
<div class="sponsor"><?php echo $facility['Facility']['sponsor']; ?></div>
<?php endif; ?>

<div class="actions">
	<ul>
<?php if ($is_admin): ?>
		<li><?php echo $this->Html->link(sprintf(__('Edit %s', true), __('Facility', true)), array('action' => 'edit', 'facility' => $facility['Facility']['id'])); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('Add %s', true), __(Configure::read('ui.field_cap'), true)), array('controller' => 'fields', 'action' => 'add', 'facility' => $facility['Facility']['id'])); ?> </li>
<?php endif; ?>
	</ul>
</div>
