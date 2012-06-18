<tr>
	<?php if ($is_admin || $is_coordinator): ?>
	<th><?php __('Rating'); ?></th>
	<?php endif; ?>
	<th><?php __('Team Name'); ?></th>
	<?php if ($division['Division']['is_playoff']): ?>
	<th><?php __('From'); ?></th>
	<?php endif; ?>
	<?php if ($is_logged_in): ?>
	<th><?php __('Players'); ?></th>
	<th><?php __('Avg. Skill'); ?></th>
	<th class="actions"><?php __('Actions');?></th>
	<?php endif; ?>
</tr>
