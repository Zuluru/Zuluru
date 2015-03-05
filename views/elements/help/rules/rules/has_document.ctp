<?php $year = date('Y'); ?>
<h4><?php __('Type: Boolean'); ?></h4>
<p><?php printf(__('The %s rule accepts an upload type id and a YYYY-MM-DD formatted date, separated by a comma, and returns true if the player has an approved document of the specified type valid on the date indicated.', true), 'HAS_DOCUMENT'); ?></p>
<p><?php __('Example:'); ?></p>
<p><?php __('If upload type 1 is a "junior waiver", then'); ?></p>
<pre>HAS_DOCUMENT(1, '<?php echo $year; ?>-06-01')</pre>
<p><?php printf(__('would return true if the person has a junior waiver approved for a date range that encompasses June 1, %s, false otherwise.', true), $year); ?></p>
