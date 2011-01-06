<?php
$spirit = $this->element ("spirit/view/{$spirit_obj->render_element}",
	compact ('team', 'league', 'spirit', 'spirit_obj'));

if ($spirit) {
	echo $this->Html->tag ('fieldset',
		$this->Html->tag ('legend', __('Spirit assigned to', true) . ' ' . $team['name']) . $spirit);
}
?>