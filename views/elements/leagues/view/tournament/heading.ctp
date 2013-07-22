<tr>
	<?php if ($is_admin || $is_manager || $is_coordinator): ?>
	<th><?php __('Initial Seed'); ?></th>
	<?php endif; ?>
	<th><?php __('Team Name'); ?></th>
	<?php if ($division['is_playoff']): ?>
	<th><?php __('From'); ?></th>
	<?php endif; ?>
	<?php if ($is_logged_in): ?>
	<th><?php __('Players'); ?></th>
	<?php if (Configure::read('profile.skill_level')): ?>
	<th><?php __('Avg. Skill'); ?></th>
	<?php endif; ?>
	<?php endif; ?>
	<th class="actions"><?php __('Actions');?></th>
</tr>
