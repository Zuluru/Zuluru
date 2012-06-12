<?php $year = date('Y'); ?>
<h4>Type: Boolean</h4>
<p>The HAS_DOCUMENT rule accepts an upload type id and a YYYY-MM-DD formatted date, separated by a comma, and returns true if the player has an approved document of the specified type valid on the date indicated.</p>
<p>Example:</p>
<p>If upload type 1 is a "junior waiver", then</p>
<pre>HAS_DOCUMENT(1, '<?php echo $year; ?>-06-01')</pre>
<p>would return true if the person has a junior waiver approved for a date range that encompasses June 1, <?php echo $year; ?>, false otherwise.</p>