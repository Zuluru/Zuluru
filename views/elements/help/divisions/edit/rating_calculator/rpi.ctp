<p><?php __('The Rating Percentage Index is a simple system for rating teams based on their winning percentage, their opponents\' winning percentage, and their opponents\' opponents\' winning percentage. It is commonly applied to NCAA basketball and baseball.'); ?></p>
<p><?php __('With the RPI system, ratings are re-calculated on a daily basis, taking into account the strength of each team\'s schedule.'); ?></p>
<?php if ($is_admin): ?>
<p class="warning-message"><?php __('NOTE: For ratings to be re-calculated, you MUST have a daily cron job set up as described in the README file.'); ?></p>
<p><?php
printf(__('Details are %s.', true),
	$this->Html->link(__('here', true), 'http://en.wikipedia.org/wiki/Ratings_Percentage_Index')
);
?></p>
<?php endif; ?>
