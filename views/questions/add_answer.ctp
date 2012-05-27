<?php
if (isset ($answer)) {
	echo $this->element('/question/edit_answer', compact('answer', 'i'));
}
?>
