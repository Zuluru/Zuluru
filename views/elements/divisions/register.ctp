<?php if (!empty($events)): ?>
<div class="related">
	<h3><?php __('Register to play in this division:');?></h3>
	<table class="list">
	<tr>
		<th><?php __('Registration'); ?></th>
		<th><?php __('Type');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($events as $related):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $this->Html->link($related['name'], array('controller' => 'events', 'action' => 'view', 'event' => $related['id']));?></td>
			<td><?php __($related['EventType']['name']);?></td>
		</tr>
	<?php endforeach; ?>
	</table>
</div>
<?php endif; ?>
