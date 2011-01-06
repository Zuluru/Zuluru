<?php
$this->Html->addCrumb (__('Fields', true));
$this->Html->addCrumb (__('List', true));
?>

<div class="fields index">
<h2><?php __($closed ? 'Closed Fields List' : 'Fields List');?></h2>
<?php if (!$closed) echo $this->element('fields/caution'); ?>

<!-- p>There is also a <?php echo $this->Html->link(__('map of all fields', true), array('controller' => 'maps')); ?> available.</p -->

<table>
<tr>
	<th><?php __('Field'); ?></th>
	<th><?php __('Actions'); ?></th>
</tr>

<?php
$i = 0;
$last_name = null;
foreach ($fields as $field):
	if ($field['Region']['name'] != $last_name) {
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
		echo "<tr$class><td colspan='2'><h3>{$field['Region']['name']}</h3></td></tr>";
		$last_name = $field['Region']['name'];
	}
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $this->Html->link(__($field['Field']['name'], true), array('action' => 'view', 'field' => $field['Field']['id'])); ?>
		</td>
		<td class="actions">
			<?php echo $this->Html->link(__('Layout', true), array('controller' => 'maps', 'action' => 'view', 'field' => $field['Field']['id']), array('target' => '_new')); ?>
			<?php echo $this->Html->link(__('Bookings', true), array('action' => 'bookings', 'field' => $field['Field']['id'])); ?>
<?php if ($is_admin): ?>
			<?php echo $this->Html->link(sprintf(__('Edit %s', true), __('Field', true)), array('action' => 'edit', 'field' => $field['Field']['id'])); ?>
			<?php echo $this->Html->link(sprintf(__('Edit %s', true), __('Layout', true)), array('controller' => 'maps', 'action' => 'edit', 'field' => $field['Field']['id'])); ?>
			<?php echo $this->Html->link(sprintf(__('Add %s', true), __('Game Slots', true)), array('controller' => 'game_slots', 'action' => 'add', 'field' => $field['Field']['id'])); ?>
			<?php $id = 'span_' . mt_rand(); ?>
			<span id="<?php echo $id; ?>">
			<?php
			if ($field['Field']['is_open']) {
				echo $this->Js->link(__('Close', true),
						array('action' => 'close', 'field' => $field['Field']['id'], 'id' => $id),
						array('update' => "#temp_update")
				);
			} else {
				echo $this->Js->link(__('Open', true),
						array('action' => 'open', 'field' => $field['Field']['id'], 'id' => $id),
						array('update' => "#temp_update")
				);
			}
			?>
			</span>
<?php endif; ?>
		</td>
	</tr>
<?php endforeach; ?>

</table>
</div>
<div id="temp_update" style="display: none;"></div>
