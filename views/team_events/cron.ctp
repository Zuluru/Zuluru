<?php
echo $this->Html->tag('h2', __('Team Event Attendance', true));

echo $this->Html->para(null, sprintf(__('%s attendance reminders sent', true), $remind_count));
echo $this->Html->para(null, sprintf(__('%s attendance summaries sent', true), $summary_count));

?>
