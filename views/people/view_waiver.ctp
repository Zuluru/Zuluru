<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb ($person['Person']['full_name']);
$this->Html->addCrumb (__('View Waiver', true));
?>

<?php
echo $this->element ("people/waiver/$type");
if ($waiver) {
	echo $this->Html->tag ('strong', $this->Html->para (null, sprintf (__('You accepted this waiver at %s on %s.', true),
			$this->ZuluruTime->time($waiver['created']), $this->ZuluruTime->fulldate($waiver['created']))));
} else {
	echo $this->Html->para (null, __('You haven\'t accepted this waiver.'));
}
?>
