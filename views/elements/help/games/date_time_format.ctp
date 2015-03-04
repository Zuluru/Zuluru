<?php
$date = mktime(20, 0, 0);
$time = $this->Time->format(reset(Configure::read('options.time_formats')), $date);
$day = $this->Time->format(reset(Configure::read('options.day_formats')), $date);
$date = $this->Time->format(reset(Configure::read('options.date_formats')), $date);
?>
<p><?php printf(__('By default, this system formats 8PM as %s, and today as either %s or %s (depending on the context). %s to change these settings to something that suits you better (e.g. many people prefer 12 hour format to 24 hour).', true),
	$time, $day, $date, $this->Html->link(__('Edit your preferences', true), array('controller' => 'people', 'action' => 'preferences'))
); ?></p>
<?php if ($is_admin): ?>
<p><?php __('You can change the system defaults and/or add new options by altering the order of and/or adding to the date_formats, day_formats and time_formats values in config/options.php.'); ?></p>
<?php endif; ?>
