<?php $year = date('Y'); ?>
<h4><?php __('Type: Boolean'); ?></h4>
<p><?php printf(__('The %s rule accepts a comma-separated list of two or more other rules, returning <em>true</em> if all of them are true, <em>false</em> otherwise.', true), 'AND'); ?></p>
<p><?php __('Example:'); ?></p>
<pre>AND(
    COMPARE(ATTRIBUTE('gender') = 'Male'),
    COMPARE(ATTRIBUTE('birthdate') &lt;= '<?php echo $year - 33; ?>-12-31')
)</pre>
<p><?php
printf(__('will return <em>true</em> if the player is male and was born on or before Dec 31, %s (i.e. is a male masters player in %s), <em>false</em> otherwise.', true),
	$year - 33, $year
); ?></p>
<pre>AND(
    COMPARE(ATTRIBUTE('gender') = 'Female'),
    COMPARE(ATTRIBUTE('birthdate') &lt;= '<?php echo $year - 30; ?>-12-31')
)</pre>
<p><?php printf(__('will return <em>true</em> if the player is female and was born on or before Dec 31, %s (i.e. is a female masters player in %s), <em>false</em> otherwise.', true), $year - 30, $year); ?></p>
