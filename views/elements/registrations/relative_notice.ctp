<?php
$relatives = $this->UserCache->allActAs();
$url = array_merge(array('action' => $this->action), $this->params['named']);
$links = array();
if (!empty($relatives)) {
	foreach ($relatives as $id => $relative) {
		$show = $this->requestAction(array('controller' => 'registrations', 'action' => 'show'), array('named' => array('person' => $id)));
		if ($show) {
			$url['act_as'] = $id;
			$links[$id] = $this->Html->link($relative, $url);
		}
	}
}

if (!empty($links)) {
	echo $this->Html->para(null, sprintf(__('Note that you are registering %s. To register %s instead, click on their name.', true),
			$this->UserCache->read('Person.full_name'),
			implode(' ' . __('or', true) . ' ', $links)));
}