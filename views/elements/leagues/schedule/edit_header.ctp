<tr>
	<th colspan="3"><a name="<?php echo $date; ?>"><?php echo $this->ZuluruTime->fulldate($date); ?></a></th>
	<th colspan="<?php echo 2 + !$competition; ?>" class="actions splash_action">
	<?php echo $this->ZuluruHtml->iconLink('field_24.png',
			array('action' => 'slots', $id_field => $id, 'date' => $date),
			array('alt' => __(Configure::read('sport.fields_cap'), true), 'title' => sprintf(__('Available %s', true), __(Configure::read('sport.fields_cap'), true)))); ?>
	</th>
</tr>
