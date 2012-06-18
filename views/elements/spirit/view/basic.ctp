<?php
if ($spirit && ($is_admin || $is_coordinator || $league['display_sotg'] === 'all')) {
	echo $this->element('formbuilder/view', array('questions' => $spirit_obj->questions, 'answers' => $spirit));
}
?>
