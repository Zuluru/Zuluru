<?php
if ($is_logged_in) {
	if (!empty($photo['Upload'])) {
		$upload_dir = Configure::read('folders.uploads');
		if (file_exists ($upload_dir . DS . $photo['Upload']['filename'])) {
			echo $this->Html->image(
					Router::url (array('controller' => 'people', 'action' => 'photo', 'person' => $photo['Upload']['person_id'])),
					array('class' => 'thumbnail profile_photo', 'title' => "{$person['first_name']} {$person['last_name']}")
			);
		}
	} else if (Configure::read('feature.gravatar')) {
		$url = 'http://www.gravatar.com/avatar/';
		if ($person['show_gravatar']) {
			$url .= md5(strtolower($person['email']));
		} else {
			$url .= '00000000000000000000000000000000';
		}
		$url .= "?s=150&d=mm&r=pg";
		echo $this->Html->image($url, array('class' => 'thumbnail profile_photo', 'title' => "{$person['first_name']} {$person['last_name']}"));
	}
}
?>
