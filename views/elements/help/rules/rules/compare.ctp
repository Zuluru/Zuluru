<?php $year = date('Y'); ?>
<h4><?php __('Type: Boolean'); ?></h4>
<p><?php printf(__('The %s rule accepts two other rules, separated by a comparison operator, and returns the result of performing a boolean comparison of the results of executing the two rules.', true), 'COMPARE'); ?></p>
<p><?php __('The comparison operator must have whitespace on both sides of it.'); ?></p>
<p><?php __('Possible comparison operators are:'); ?></p>
<ul>
<li>= (<?php __('test for equality'); ?>)</li>
<li>!= (<?php __('test for inequality'); ?>)</li>
<li>&lt; (<?php __('less than'); ?>)</li>
<li>&lt;= (<?php __('less than or equal to'); ?>)</li>
<li>&gt; (<?php __('greater than'); ?>)</li>
<li>&gt;= (<?php __('greater than or equal to'); ?>)</li>
</ul>
<p><?php __('Note that comparisons are done in a case-sensitive fashion.'); ?></p>
<p><?php __('Example:'); ?></p>
<pre>COMPARE(ATTRIBUTE('gender') = 'Male')</pre>
<p><?php __('will return <em>true</em> if the player is male, <em>false</em> otherwise.'); ?></p>
<pre>COMPARE(ATTRIBUTE('gender') = 'male')</pre>
<p><?php __('will <strong>always</strong> return <em>false</em>, because the "gender" attribute is always capitalized.'); ?></p>
<pre>COMPARE(ATTRIBUTE('birthdate') &gt;= '<?php echo $year - 18; ?>-01-01')</pre>
<p><?php printf(__('will return <em>true</em> if the player was born on or after Jan 1, %s (i.e. is a junior player in %s), <em>false</em> otherwise.', true), $year - 18, $year); ?></p>
<pre>COMPARE(MEMBER_TYPE('<?php echo $year; ?>-04-01') = 'none')</pre>
<p><?php printf(__('will return <em>true</em> if the player does <strong>not</strong> have a paid membership that covers Apr 1, %s, <em>false</em> otherwise.', true), $year); ?></p>
