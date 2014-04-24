<tr>
	<th colspan="<?php echo 3 + $multi_day; ?>"><a name="<?php echo $week[0]; ?>"><?php echo $this->ZuluruTime->displayRange($week[0], $week[1]); ?></a></th>
	<th colspan="<?php echo 2 + !$competition; ?>" class="actions splash_action"><?php
	if (!$finalized && ($is_admin || $is_manager || $is_coordinator)):
	?>
		<?php
		if (isset($division) && $has_dependent_games) {
			echo $this->ZuluruHtml->iconLink('initialize_24.png',
					array('controller' => 'divisions', 'action' => 'initialize_dependencies', $id_field => $id, 'date' => $week[0]),
					array('alt' => __('Initialize', true), 'title' => __('Initialize schedule dependencies', true)));
			echo $this->ZuluruHtml->iconLink('reset_24.png',
					array('controller' => 'divisions', 'action' => 'initialize_dependencies', $id_field => $id, 'date' => $week[0], 'reset' => true),
					array('alt' => __('Reset', true), 'title' => __('Reset schedule dependencies', true)));
		}
		?>
		<?php echo $this->ZuluruHtml->iconLink('field_24.png',
					array('action' => 'slots', $id_field => $id, 'date' => $week[0]),
					array('alt' => __(Configure::read('sport.fields_cap'), true), 'title' => sprintf(__('Available %s', true), __(Configure::read('sport.fields_cap'), true)))); ?>
		<?php echo $this->ZuluruHtml->iconLink('edit_24.png',
					array('action' => 'schedule', $id_field => $id, 'edit_date' => $week[0], '#' => $week[0]),
					array('alt' => __('Edit Week', true), 'title' => __('Edit Week', true))); ?>
		<?php echo $this->ZuluruHtml->iconLink('delete_24.png',
					array('controller' => 'schedules', 'action' => 'delete', $id_field => $id, 'date' => $week[0]),
					array('alt' => __('Delete Week', true), 'title' => __('Delete Week', true))); ?>
		<?php
		if (isset($division)) {
			echo $this->ZuluruHtml->iconLink('reschedule_24.png',
					array('controller' => 'schedules', 'action' => 'reschedule', 'division' => $division['Division']['id'], 'date' => $week[0]),
					array('alt' => __('Reschedule', true), 'title' => __('Reschedule', true)));
		}
		?>
		<?php
		if ($published) {
			echo $this->ZuluruHtml->iconLink('unpublish_24.png',
					array('controller' => 'schedules', 'action' => 'unpublish', $id_field => $id, 'date' => $week[0]),
					array('alt' => __('Unpublish', true), 'title' => __('Unpublish', true)));
		} else {
			echo $this->ZuluruHtml->iconLink('publish_24.png',
					array('controller' => 'schedules', 'action' => 'publish', $id_field => $id, 'date' => $week[0]),
					array('alt' => __('Publish', true), 'title' => __('Publish', true)));
		}
		?>
	<?php
	else:
		echo '&nbsp;';
	endif;
	?></th>
</tr>
