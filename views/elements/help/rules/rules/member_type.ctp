<?php $year = date('Y'); ?>
<h4><?php __('Type: Data'); ?></h4>
<p><?php printf(__('The %s rule accepts a YYYY-MM-DD formatted date and returns a string describing the highest membership in effect for the player on that date. It can also accept date ranges in three forms:', true), 'MEMBER_TYPE'); ?></p>
<ul>
<li><?php __('YYYY-MM-DD,YYYY-MM-DD: Looks for the highest membership in effect at any time between the dates specified (inclusive)'); ?></li>
<li>&lt;<?php __('YYYY-MM-DD: Looks for the highest membership in effect at any time up to and including the date specified (equivalent to 0000-00-00,YYYY-MM-DD)'); ?></li>
<li>&gt;<?php __('YYYY-MM-DD: Looks for the highest membership in effect at any time starting from the date specified (equivalent to YYYY-MM-DD,9999-12-31)'); ?></li>
</ul>
<p><?php __('The date specification must be enclosed in quotes.'); ?></p>
<p><?php __('Currently, the possible member types are "none" (they do not have a membership in effect on the given date), "intro" or "full". The membership type and valid dates are determined from the configuration of the membership events that the player has registered <strong>and paid</strong> for.'); ?></p>
<p><?php __('Example:'); ?></p>
<pre>MEMBER_TYPE('<?php echo $year; ?>-06-01')</pre>
<p><?php __('would return one of <strong>none</strong>, <strong>intro</strong> or <strong>full</strong>, depending on the membership registration spanning June 1 of this year (if any) found in the player\'s history.'); ?></p>
<pre>MEMBER_TYPE('&lt;<?php echo $year; ?>-06-01')</pre>
<p><?php __('would return one of <strong>none</strong>, <strong>intro</strong> or <strong>full</strong>, depending on the membership registrations up to and including June 1 of this year (if any) found in the player\'s history.'); ?></p>
<pre>MEMBER_TYPE('<?php echo $year-5; ?>-06-01,<?php echo $year; ?>-06-01')</pre>
<p><?php __('would return one of <strong>none</strong>, <strong>intro</strong> or <strong>full</strong>, depending on the membership registrations covering the 5 years up to and including June 1 of this year (if any) found in the player\'s history.'); ?></p>
<?php // TODO: Include suggestions for how to configure membership events and rules for consistency ?>
