<h2><?php
echo $field['Field']['long_name'];
?></h2>
<dl>
	<dt><?php __('Address'); ?></dt>
	<dd><?php echo $field['Facility']['location_street']; ?></dd>
	<dt><?php __('City'); ?></dt>
	<dd><?php echo $field['Facility']['location_city']; ?></dd>
	<dt><?php __('Region'); ?></dt>
	<dd><?php echo $field['Facility']['Region']['name']; ?></dd>
	<dt><?php __('Surface'); ?></dt>
	<dd><?php __(Configure::read("options.surface.{$field['Field']['surface']}")); ?></dd>
<?php if ($field['Field']['length'] > 0): ?>
	<dt><?php __('Map'); ?></dt>
	<dd><?php echo $this->Html->link (__('Open in new window', true),
		array('controller' => 'maps', 'action' => 'view', 'field' => $field['Field']['id']),
		array('target' => 'map')); ?></dd>
<?php endif; ?>

<?php if (!empty ($field['Field']['layout_url'])): ?>
	<dt><?php __('Layout'); ?></dt>
	<dd><?php echo $this->Html->link (__('Open in new window', true),
		$field['Field']['layout_url'],
		array('target' => 'map')); ?></dd>
<?php endif; ?>

<?php if (!empty ($field['Field']['permit_url'])): ?>
	<dt><?php printf(__('%s&nbsp;Permit', true), Configure::read('ui.field_cap')); ?></dt>
	<dd><?php echo $this->Html->link ($field['Field']['permit_name'],
		$field['Field']['permit_url']); ?></dd>
<?php endif; ?>
</dl>
