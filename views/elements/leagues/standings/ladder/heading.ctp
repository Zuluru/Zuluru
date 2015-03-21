<tr>
	<th rowspan="2"><?php __('Seed'); ?></th>
	<th rowspan="2"><?php __('Team Name'); ?></th>
	<th rowspan="2"><?php __('Rating'); ?></th>
	<th colspan="7"><?php __('Season To Date'); ?></th>
	<th rowspan="2"><?php __('Streak'); ?></th>
	<?php if (League::hasSpirit($league)): ?>
	<th rowspan="2"><?php __('Spirit'); ?></th>
	<?php endif; ?>
	<?php if (League::hasCarbonFlip($league)): ?>
	<th colspan="4"><?php __('Carbon Flip'); ?></th>
	<?php endif; ?>
</tr>
<tr>
	<th title="<?php __('Wins'); ?>"><?php __('W'); ?></th>
	<th title="<?php __('Losses'); ?>"><?php __('L'); ?></th>
	<th title="<?php __('Ties'); ?>"><?php __('T'); ?></th>
	<th title="<?php __('Defaults'); ?>"><?php __('D'); ?></th>
	<th title="<?php __('Goals For'); ?>"><?php __('GF'); ?></th>
	<th title="<?php __('Goals Against'); ?>"><?php __('GA'); ?></th>
	<th title="<?php __('Plus/Minus'); ?>"><?php __('+/-'); ?></th>
	<?php if (League::hasCarbonFlip($league)): ?>
	<th title="<?php __('Wins'); ?>"><?php __('W'); ?></th>
	<th title="<?php __('Losses'); ?>"><?php __('L'); ?></th>
	<th title="<?php __('Ties'); ?>"><?php __('T'); ?></th>
	<th title="<?php __('Average'); ?>"><?php __('A'); ?></th>
	<?php endif; ?>
</tr>
