<?php
$this->Html->addCrumb (__(Configure::read('ui.fields_cap'), true));
$this->Html->addCrumb ($field['Field']['long_name']);
$this->Html->addCrumb (__('View', true));
?>

<div class="fields view">
<h2><?php echo $field['Field']['long_name'];?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Facility'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php
			echo $this->Html->link($field['Facility']['name'], array('controller' => 'facilities', 'action' => 'view', 'facility' => $field['Facility']['id']));
			echo $this->ZuluruHtml->iconLink('view_24.png', array('controller' => 'facilities', 'action' => 'view', 'facility' => $field['Facility']['id']), array('id' => 'FacilityDetailsIcon'));
			$this->Js->get('#FacilityDetailsIcon')->event('click', 'jQuery("#FacilityDetails").toggle();');
			?>

		</dd>
		<fieldset id="FacilityDetails" style="display:none;">
		<legend><?php __('Facility Details'); ?></legend>
		<dl><?php $j = 1; ?>
			<dt<?php if ($j % 2 == 0) echo $class;?>><?php __('Name'); ?></dt>
			<dd<?php if ($j++ % 2 == 0) echo $class;?>>
				<?php echo $field['Facility']['name']; ?>

			</dd>
			<dt<?php if ($j % 2 == 0) echo $class;?>><?php __('Code'); ?></dt>
			<dd<?php if ($j++ % 2 == 0) echo $class;?>>
				<?php echo $field['Facility']['code']; ?>

			</dd>
			<dt<?php if ($j % 2 == 0) echo $class;?>><?php __('Region'); ?></dt>
			<dd<?php if ($j++ % 2 == 0) echo $class;?>>
				<?php __($field['Facility']['Region']['name']); ?>

			</dd>
<?php if (!empty ($field['Facility']['location_street'])): ?>
			<dt<?php if ($j % 2 == 0) echo $class;?>><?php __('Address'); ?></dt>
			<dd<?php if ($j++ % 2 == 0) echo $class;?>>
				<?php echo $field['Facility']['location_street']; ?>
				&nbsp;
			</dd>
			<dt<?php if ($j % 2 == 0) echo $class;?>><?php __('City'); ?></dt>
			<dd<?php if ($j++ % 2 == 0) echo $class;?>>
				<?php echo $field['Facility']['location_city']; ?>
				&nbsp;
			</dd>
			<dt<?php if ($j % 2 == 0) echo $class;?>><?php __('Province'); ?></dt>
			<dd<?php if ($j++ % 2 == 0) echo $class;?>>
				<?php echo $field['Facility']['location_province']; ?>
				&nbsp;
			</dd>
<?php endif; ?>
		</dl>
		</fieldset>

		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Number'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $field['Field']['num']; ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Status'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __($field['Field']['is_open'] ? 'Open' : 'Closed'); ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Indoor'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __($field['Field']['indoor'] ? 'Yes' : 'No'); ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Surface'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __(Configure::read("options.surface.{$field['Field']['surface']}")); ?>

		</dd>
<?php if ($is_admin): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Rating'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $field['Field']['rating']; ?>

		</dd>
<?php endif; ?>

<?php
$mapurl = null;
if ($field['Field']['length'] > 0) {
	$mapurl = array('controller' => 'maps', 'action' => 'view', 'field' => $field['Field']['id']);
}
?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Map'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo ($mapurl ? $this->Html->link (__('Click for map in new window', true), $mapurl, array('target' => 'map')) : __('N/A', true)); ?>

		</dd>
<?php if (!empty ($field['Field']['layout_url'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Layout'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link (sprintf(__('Click for %s layout diagram in new window', true), Configure::read('ui.field')), $field['Field']['layout_url'], array('target' => 'map')); ?>

		</dd>
<?php endif; ?>
<?php if (!empty ($field['Field']['permit_url'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php printf(__('%s&nbsp;Permit', true), Configure::read('ui.field_cap')); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link ($field['Field']['permit_name'], $field['Field']['permit_url'], array('target' => 'permit')); ?>

		</dd>
<?php endif; ?>

<?php if (!empty ($field['Facility']['Field'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php printf(__('Other %s at this facility', true), Configure::read('ui.fields')); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<table class="list">
			<tr>
				<th><?php __(Configure::read('ui.field_cap')); ?></th>
			</tr>
			<?php
			foreach ($field['Facility']['Field'] as $related) {
				// TODO: Any admin links we want to add here?
				echo $this->Html->tag('tr',
						$this->Html->tag('td',
							$this->Html->link("{$field['Facility']['name']} {$related['num']}", array('action' => 'view', 'field' => $related['id']))
				));
			}
			?>

			</table>
		</dd>
<?php endif; ?>
	</dl>
</div>
<?php if (!empty ($field['Facility']['sponsor'])): ?>
<div class="sponsor"><?php echo $field['Facility']['sponsor']; ?></div>
<?php endif; ?>

<div class="actions">
	<ul>
<?php if ($is_admin): ?>
		<li><?php echo $this->Html->link(sprintf(__('Edit %s', true), __(Configure::read('ui.field_cap'), true)), array('action' => 'edit', 'field' => $field['Field']['id'], 'return' => true)); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('Edit %s', true), __('Layout', true)), array('controller' => 'maps', 'action' => 'edit', 'field' => $field['Field']['id'], 'return' => true)); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('Add %s', true), __('Game Slots', true)), array('controller' => 'game_slots', 'action' => 'add', 'field' => $field['Field']['id'])); ?> </li>
<?php endif; ?>
		<li><?php echo $this->Html->link(sprintf(__('View %s', true), __('Bookings ', true)), array('action' => 'bookings', 'field' => $field['Field']['id'])); ?> </li>
	</ul>
</div>
