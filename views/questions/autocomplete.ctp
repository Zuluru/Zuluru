<?php
foreach($questions as $question) {
	echo trim ($question['Question']['question']) . '|' .
		$question['Question']['id'] . "\n";
}
?>