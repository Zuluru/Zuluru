<tr>
	<th colspan="3"><a name="<?php echo $date; ?>"><?php echo $this->ZuluruTime->fulldate($date); ?></a></th>
	<th colspan="<?php echo 2 + !$competition; ?>" class="actions splash_action">
	<?php echo $this->ZuluruHtml->iconLink('field_24.png',
			array('action' => 'slots', $id_field => $id, 'date' => $date),
			array('alt' => __(Configure::read('sport.fields_cap'), true), 'title' => sprintf(__('Available %s', true), __(Configure::read('sport.fields_cap'), true)))); ?>
	</th>
</tr>
<tr>
	<th><?php if ($is_tournament): ?><?php __('Game'); ?><?php endif; ?></th>
	<th colspan="2"><?php printf(__('Time/%s', true), __(Configure::read('sport.field_cap'), true)); ?></th>
	<th><?php __($competition ? 'Team' : 'Home'); ?></th>
	<?php if (!$competition): ?>
	<th><?php __('Away'); ?></th>
	<?php endif; ?>
	<th></th>
</tr>

