<?php $year = date('Y'); ?>
<h4><?php __('Type: Boolean'); ?></h4>
<p><?php printf(__('The %s rule accepts a series of one or more waiver IDs and a YYYY-MM-DD formatted date, separated by commas, and returns true if the player has a signed one of the specified waivers covering the date indicated.', true), 'SIGNED_WAIVER'); ?></p>
<p class="warning-message"><?php __('Note that the order of waiver IDs is important; if the person has not signed any of the waivers, they will be directed to the <em>first</em> one in the list.'); ?></p>
<p><?php __('Example:'); ?></p>
<p><?php __('If waiver type 1 is a "membership waiver", then'); ?></p>
<pre>SIGNED_WAIVER(1, '<?php echo $year; ?>-06-01')</pre>
<p><?php printf(__('would return <em>true</em> if the person has signed the membership waiver for a date range that encompasses June 1, %s, <em>false</em> otherwise.', true), $year); ?></p>
<p><?php __('If waiver type 2 is an "event waiver", then'); ?></p>
<pre>SIGNED_WAIVER(2, 1, '<?php echo $year; ?>-06-01')</pre>
<p><?php printf(__('would return <em>true</em> if the person has signed either the membership waiver OR the event waiver for a date range that encompasses June 1, %s, <em>false</em> (and directing them to the event waiver) otherwise.', true), $year); ?></p>
