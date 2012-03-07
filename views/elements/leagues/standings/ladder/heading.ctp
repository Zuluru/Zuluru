<tr>
	<th rowspan="2"><?php __('Seed'); ?></th>
	<th rowspan="2"><?php __('Team Name'); ?></th>
	<th rowspan="2"><?php __('Rating'); ?></th>
	<th colspan="7"><?php __('Season To Date'); ?></th>
	<th rowspan="2"><?php __('Streak'); ?></th>
	<?php if (League::hasSpirit($division)): ?>
	<th rowspan="2"><?php __('Spirit'); ?></th>
	<?php endif; ?>
</tr>
<tr>
	<th><?php __('W'); ?></th>
	<th><?php __('L'); ?></th>
	<th><?php __('T'); ?></th>
	<th><?php __('D'); ?></th>
	<th><?php __('GF'); ?></th>
	<th><?php __('GA'); ?></th>
	<th><?php __('+/-'); ?></th>
</tr>
