<?php
$upload_dir = Configure::read('urls.uploads');
if (file_exists (WWW_ROOT . $upload_dir . DS . $upload['filename'])) {
	echo $this->Html->image($upload_dir . DS . $upload['filename'],
			array('class' => 'thumbnail', 'title' => $person['full_name']));
}
?>
