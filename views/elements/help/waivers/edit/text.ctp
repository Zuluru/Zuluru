<?php $date = date('Y'); ?>
<p>Waiver text can include a number of variables which will be replaced when a player signs the waiver.
Available variables include:</p>
<table class="list">
	<tr><td>%name%</td><td>Your organization's full name (<?php echo Configure::read('organization.name'); ?>)</td></tr>
	<tr><td>%short_name%</td><td>Your organization's short name (<?php echo Configure::read('organization.short_name'); ?>)</td></tr>
	<tr><td>%field%</td><td rowspan="4">The sport-specific alternative for "field", and the various plural and capitalized versions of this word (<?php echo Configure::read('ui.field'); ?>/<?php echo Configure::read('ui.fields'); ?>/<?php echo Configure::read('ui.field_cap'); ?>/<?php echo Configure::read('ui.fields_cap'); ?>)</td></tr>
	<tr><td>%fields%</td></tr>
	<tr><td>%Field%</td></tr>
	<tr><td>%Fields%</td></tr>
	<tr><td>%valid_from%</td><td>First date the waiver will be valid on</td></tr>
	<tr><td>%valid_from_year%</td><td>Year of the first date the waiver will be valid on</td></tr>
	<tr><td>%valid_until%</td><td>Last date the waiver will be valid on</td></tr>
	<tr><td>%valid_until_year%</td><td>Year of the last date the waiver will be valid on</td></tr>
	<tr><td>%valid_years%</td><td>Years the waiver will be valid in (e.g. <?php echo $date; ?> or <?php echo $date; ?>-<?php echo $date + 1; ?>)</td></tr>
</table>