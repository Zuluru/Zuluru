<?php
if (empty ($leagues)) {
	echo 'No leagues operate on the selected night.';
} else {
	foreach ($leagues as $league) {
		echo $this->Form->input ("League.{$league['League']['id']}", array(
				'label' => $league['League']['long_name'],
				'type' => 'checkbox',
				'hiddenField' => false,
		));
	}
}
?>