<?php $year = date('Y'); ?>
<h4>Type: Data</h4>
<p>The FORMAT_DATE rule accepts a date in an arbitrary format and reformats into a standard YYYY-MM-DD format useful for comparisons.</p>
<p>Example:</p>
<pre>FORMAT_DATE('June 1, <?php echo $year; ?>')</pre>
<p>would return <strong><?php echo $year; ?>-06-01</strong></p>
