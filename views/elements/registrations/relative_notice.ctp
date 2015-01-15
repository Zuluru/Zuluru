<?php
$relatives = $this->UserCache->read('Relatives');
$url = array_merge(array('action' => $this->action), $this->params['named']);
$links = array();
if (!empty($relatives)) {
	foreach ($relatives as $relative) {
		$url['act_as'] = $relative['Relative']['id'];
		$links[$relative['Relative']['id']] = $this->Html->link($relative['Relative']['full_name'], $url);
	}
} else if ($this->UserCache->currentId() != $this->UserCache->realId()) {
	$url['act_as'] = $this->UserCache->realId();
	$links[$this->UserCache->realId()] = $this->Html->link($this->UserCache->read('Person.full_name', $this->UserCache->realId()), $url);
}

if (!empty($links)) {
	echo $this->Html->para(null, sprintf(__('Note that you are performing this as %s. Click the name to do it as %s instead.', true),
			$this->UserCache->read('Person.full_name'),
			implode(' ' . __('or', true) . ' ', $links)));
}