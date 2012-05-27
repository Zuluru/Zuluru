<?php
echo $this->Html->tag ('h2', __('Rosters', true));
$roster = array();
if (isset($emailed) && $emailed) {
	$roster[] = sprintf(__('%d reminders sent', true), $emailed);
}
if (isset($reminded) && $reminded) {
	$roster[] = sprintf(__('%d second reminders sent', true), $reminded);
}
if (isset($expired) && $expired) {
	$roster[] = sprintf(__('%d expired invitations/requests removed', true), $expired);
}
if (isset($outstanding) && $outstanding) {
	$roster[] = sprintf(__('%d have had reminders sent but have not yet expired', true), $outstanding);
}
if (empty($roster)) {
	echo $this->Html->para (null, __('No roster activity to report.', true));
} else {
	echo $this->Html->para (null, implode(', ', $roster) . '.');
}

?>
