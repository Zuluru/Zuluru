<?php
if (!is_array($messages)) {
	$messages = array($messages);
}

foreach ($messages as $message) {
	$class = null;
	if (is_array($message)) {
		$class = $message['class'];
		$message = $message['text'];
	}
	echo $this->Html->para ($class, $message);
}
?>