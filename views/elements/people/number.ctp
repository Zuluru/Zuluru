<?php
if ($is_admin || $is_manager || $is_captain || (isset($my_id) && $my_id == $person['id'])) {
	if (!empty($person['TeamsPerson']['number']) || $person['TeamsPerson']['number'] === '0') {
		$link_text = $person['TeamsPerson']['number'];
	} else {
		$link_text = $this->ZuluruHtml->icon('add_24.png',
			array('alt' => __('Add Number', true), 'title' => __('Add Number', true)));
	}
	$url = array('action' => 'numbers', 'team' => $team['Team']['id'], 'person' => $person['id']);
	$url_string = Router::url($url);
	echo $this->ZuluruHtml->link($link_text, $url, array(
		'escape' => false,
		'onClick' => "return change_number('$url_string', jQuery(this), '{$person['TeamsPerson']['number']}');",
	));
} else {
	echo $person['TeamsPerson']['number'];
}
?>