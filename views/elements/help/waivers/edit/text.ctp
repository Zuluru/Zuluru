<?php $date = date('Y'); ?>
<p><?php __('Waiver text can include a number of variables which will be replaced when a player signs the waiver. Available variables include:'); ?></p>
<table class="list">
	<tr><td>%name%</td><td><?php __('Your organization\'s full name'); ?> (<?php echo Configure::read('organization.name'); ?>)</td></tr>
	<tr><td>%short_name%</td><td><?php __('Your organization\'s short name'); ?> (<?php echo Configure::read('organization.short_name'); ?>)</td></tr>
	<tr><td>%field%</td><td rowspan="4"><?php __('The sport-specific alternative for "field", and the various plural and capitalized versions of this word'); ?> (<?php __(Configure::read('ui.field')); ?>/<?php __(Configure::read('ui.fields')); ?>/<?php __(Configure::read('ui.field_cap')); ?>/<?php __(Configure::read('ui.fields_cap')); ?>)</td></tr>
	<tr><td>%fields%</td></tr>
	<tr><td>%Field%</td></tr>
	<tr><td>%Fields%</td></tr>
	<tr><td>%valid_from%</td><td><?php __('First date the waiver will be valid on'); ?></td></tr>
	<tr><td>%valid_from_year%</td><td><?php __('Year of the first date the waiver will be valid on'); ?></td></tr>
	<tr><td>%valid_until%</td><td><?php __('Last date the waiver will be valid on'); ?></td></tr>
	<tr><td>%valid_until_year%</td><td><?php __('Year of the last date the waiver will be valid on'); ?></td></tr>
	<tr><td>%valid_years%</td><td><?php printf(__('Years the waiver will be valid in (e.g. %s or %s-%s)', true), $date, $date, $date + 1); ?></td></tr>
</table>
