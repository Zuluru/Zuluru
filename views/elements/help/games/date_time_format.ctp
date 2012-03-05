<?php
$date = mktime(20, 0, 0);
$time = $this->Time->format(array_shift (Configure::read('options.time_formats')), $date);
$day = $this->Time->format(array_shift (Configure::read('options.day_formats')), $date);
$date = $this->Time->format(array_shift (Configure::read('options.date_formats')), $date);
?>
<p>By default, this system formats 8PM as <?php echo $time; ?>, and today as either <?php echo $day; ?> or <?php echo $date; ?> (depending on the context).
<?php echo $this->Html->link('Edit your preferences', array('controller' => 'people', 'action' => 'preferences')); ?> to change these settings to something that suits you better (e.g. many people prefer 12 hour format to 24 hour).</p>
<?php if ($is_admin): ?>
<p>You can change the system defaults and/or add new options by altering the order of and/or adding to the date_formats, day_formats and time_formats values in config/options.php.</p>
<?php endif; ?>