<?php $year = date('Y'); ?>
<h4><?php __('Type: Data'); ?></h4>
<p><?php printf(__('The %s rule accepts a date in an arbitrary format and reformats into a standard YYYY-MM-DD format useful for comparisons.', true), 'FORMAT_DATE'); ?></p>
<p><?php __('Example:'); ?></p>
<pre>FORMAT_DATE('June 1, <?php echo $year; ?>')</pre>
<p><?php printf(__('would return <strong>%s-06-01</strong>', true), $year); ?></p>
