<?php
if (!empty($to_close)) {
	echo $this->Html->tag('h2', __('Closed the following leagues:', true));
	foreach ($to_close as $league) {
		echo $this->Html->para(null, $this->Html->link ($league['League']['long_name'],
				$this->Html->url(array('controller' => 'leagues', 'action' => 'view', 'league' => $league['League']['id']), true)));
	}
}

if (!empty($to_open)) {
	echo $this->Html->tag('h2', __('Opened the following leagues:', true));
	foreach ($to_open as $league) {
		echo $this->Html->para(null, $this->Html->link ($league['League']['long_name'],
				$this->Html->url(array('controller' => 'leagues', 'action' => 'view', 'league' => $league['League']['id']), true)));
	}
}
?>