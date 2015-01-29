<?php
$relatives = $this->UserCache->allActAs();
$url = array_merge(array('action' => $this->action), $this->params['named']);
$links = array();
if (!empty($relatives)) {
	foreach ($relatives as $id => $relative) {
		$url['act_as'] = $id;
		$links[$id] = $this->Html->link($relative, $url);
	}
}

if (!empty($links)) {
	echo $this->Html->para(null, sprintf(__('Note that you are performing this as %s. Click the name to do it as %s instead.', true),
			$this->UserCache->read('Person.full_name'),
			implode(' ' . __('or', true) . ' ', $links)));
}