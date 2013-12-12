<?php
$response = array();
foreach($questions as $question) {
	$response[] = array(
		'label' => trim ($question['Question']['question']),
		'value' => $question['Question']['id']
	);
}
echo json_encode($response);
?>