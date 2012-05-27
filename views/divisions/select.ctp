<?php
if (empty ($divisions)) {
	echo 'No divisions operate on the selected night.';
} else {
	foreach ($divisions as $division) {
		echo $this->Form->input ("Division.{$division['Division']['id']}", array(
				'label' => $division['Division']['full_league_name'],
				'type' => 'checkbox',
				'hiddenField' => false,
		));
	}
}
?>