<tr>
	<th><?php __('Team Name'); ?></th>
	<?php if ($is_logged_in): ?>
	<th><?php __('Players'); ?></th>
	<?php if (Configure::read('profile.skill_level')): ?>
	<th><?php __('Avg. Skill'); ?></th>
	<?php endif; ?>
	<?php endif; ?>
	<th class="actions"><?php __('Actions');?></th>
</tr>
