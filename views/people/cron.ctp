<?php
if ($emailed > 0) {
	echo $this->Html->tag ('h2', __('Membership welcome letters', true));
	echo $this->Html->para (null, sprintf (__('Emailed %d membership letters.', true), $emailed));
}
?>
