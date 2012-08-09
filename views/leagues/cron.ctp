<?php
$title = $this->Html->tag('h2', __('Made the following ratings adjustments:', true));

foreach ($leagues as $league) {
	foreach ($league['Division'] as $division) {
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
					$this->Html->link($league['League']['full_name'] . ' ' . $division['name'], $this->Html->url (array('controller' => 'divisions', 'action' => 'view', 'division' => $division['id']), true)) . ' ' .
					': ' . __('Adjusted', true) . ' ' .
					implode(__(' and ', true), $adjustments) . '.');
		}
	}
}

if (!empty($to_close)) {
	echo $this->Html->tag('h2', __('Closed the following divisions:', true));
	foreach ($to_close as $division) {
		echo $this->Html->para(null, $this->Html->link ($division['Division']['full_league_name'],
				$this->Html->url(array('controller' => 'divisions', 'action' => 'view', 'division' => $division['Division']['id']), true)));
	}
}

if (!empty($to_open)) {
	echo $this->Html->tag('h2', __('Opened the following divisions:', true));
	foreach ($to_open as $division) {
		echo $this->Html->para(null, $this->Html->link ($division['Division']['full_league_name'],
				$this->Html->url(array('controller' => 'divisions', 'action' => 'view', 'division' => $division['Division']['id']), true)));
	}
}
?>