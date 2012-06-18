<?php
$title = $this->Html->tag('h2', __('Made the following ratings adjustments:', true));

foreach ($divisions as $division) {
	if (!empty($division['updates']) && array_sum($division['updates']) > 0) {
		echo $title;
		$title = null;

		$adjustments = array();
		if ($division['updates'][0] > 0) {
			$adjustments[] = $division['updates'][0] . ' ' . __('teams', true);
		}
		if ($division['updates'][1] > 0) {
			$adjustments[] = $division['updates'][1] . ' ' . __('games', true);
		}
		echo $this->Html->para(null,
				$this->Html->link($division['Division']['long_league_name'], $this->Html->url (array('action' => 'view', 'division' => $division['Division']['id']), true)) . ' ' .
				': ' . __('Adjusted', true) . ' ' .
				implode(__(' and ', true), $adjustments) . '.');
	}
}
?>
