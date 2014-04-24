<tr>
	<th colspan="<?php echo 3 + $multi_day; ?>"><a name="<?php echo $week[0]; ?>"><?php echo $this->ZuluruTime->displayRange($week[0], $week[1]); ?></a></th>
	<th colspan="<?php echo 2 + !$competition; ?>" class="actions splash_action">
	<?php echo $this->ZuluruHtml->iconLink('field_24.png',
			array('action' => 'slots', $id_field => $id, 'date' => $week[0]),
			array('alt' => __(Configure::read('sport.fields_cap'), true), 'title' => sprintf(__('Available %s', true), __(Configure::read('sport.fields_cap'), true)))); ?>
	</th>
</tr>
