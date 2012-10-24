<?php
if (isset ($answer)) {
	echo $this->element('/questions/edit_answer', compact('answer', 'i'));
}
?>
