<?php
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